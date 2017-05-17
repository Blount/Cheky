<?php

set_time_limit(0);

use AdService\SiteConfigFactory;

class Main
{
    /**
     * @var HttpClientAbstract
     */
    protected $_httpClient;

    /**
     * @var Config_Lite
     */
    protected $_config;

    /**
     * @var PHPMailer
     */
    protected $_mailer;

    /**
     * @var App\User\Storage
     */
    protected $_userStorage;

    protected $_lockFile;
    protected $_logger;
    protected $_running = false;

    public function __construct(
        \Config_Lite $config,
        \HttpClientAbstract $client,
        \App\Storage\User $userStorage)
    {
        $this->_config = $config;

        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, array($this, "sigHandler"));
            pcntl_signal(SIGINT, array($this, "sigHandler"));
        }

        $this->_httpClient = $client;
        $this->_userStorage = $userStorage;
        $this->_logger = Logger::getLogger("main");
        $this->_lockFile = DOCUMENT_ROOT."/var/.lock";

        $this->_lock();
        $this->_running = true;

        $this->_logger->info("[Pid ".getmypid()."] Vérification des alertes.");

        $this->_mailer = new PHPMailer($exceptions=true);
        $this->_mailer->setLanguage("fr", DOCUMENT_ROOT."/lib/PHPMailer/language/");
        $this->_mailer->CharSet = "utf-8";
        if ($config->hasSection("mailer")) {
            if ($smtp = $config->get("mailer", "smtp", array())) {
                $this->_mailer->SMTPKeepAlive = true;
                if (!empty($smtp["host"])) {
                    $this->_mailer->Host = $smtp["host"];
                    if (!empty($smtp["port"])) {
                        $this->_mailer->Port = $smtp["port"];
                    }
                    $this->_mailer->isSMTP();
                }
                if (!empty($smtp["username"])) {
                    $this->_mailer->SMTPAuth = true;
                    $this->_mailer->Username = $smtp["username"];
                }
                if (!empty($smtp["password"])) {
                    $this->_mailer->SMTPAuth = true;
                    $this->_mailer->Password = $smtp["password"];
                }
                if (!empty($smtp["secure"])) {
                    $this->_mailer->SMTPSecure = $smtp["secure"];
                }
            }
            if ($from = $config->get("mailer", "from", null)) {
                $this->_mailer->Sender = $from;
                $this->_mailer->From = $from;
                $this->_mailer->FromName = $from;
            }
        }
        $this->_mailer->isHTML(true);
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    public function check()
    {
        $checkStart = (int)$this->_config->get("general", "check_start", 7);
        $checkEnd = (int)$this->_config->get("general", "check_end", 24);
        if ($checkStart > 23) {
            $checkStart = 0;
        }
        if ($checkEnd < 1) {
            $checkEnd = 24;
        }
        $hour = (int)date("G");
        if ($hour < $checkStart || $hour >= $checkEnd) {
            $this->_logger->info("[Pid ".getmypid()."] Hors de la plage horaire. Contrôle annulé.");
            return;
        }
        $this->_checkConnection();
        $users = $this->_userStorage->fetchAll();

        // génération d'URL court pour les SMS
        $curlTinyurl = curl_init();
        curl_setopt($curlTinyurl, CURLOPT_RETURNTRANSFER, 1);

        $storageType = $this->_config->get("storage", "type", "files");

        $baseurl = $this->_config->get("general", "baseurl", "");

        foreach ($users AS $user) {
            if ($storageType == "db") {
                $storage = new \App\Storage\Db\Alert($this->_userStorage->getDbConnection(), $user);
                $this->_logger->info("[Pid ".getmypid()."] USER : ".$user->getUsername());
            } else {
                $file = DOCUMENT_ROOT."/var/configs/".$user->getUsername().".csv";
                if (!is_file($file)) {
                    continue;
                }
                $storage = new \App\Storage\File\Alert($file);
                $this->_logger->info("[Pid ".getmypid()."] USER : ".$user->getUsername()." (".$file.")");
            }

            $reset_filename = DOCUMENT_ROOT."/var/tmp/reset_".$user->getId();
            $reset = is_file($reset_filename);
            if ($reset) {
                unlink($reset_filename);
            }

            // configuration des notifications.
            $notifications = array();
            $notifications_enabled = $user->getNotificationsEnabled();
            foreach ($notifications_enabled AS $notification_name => $options) {
                if (!is_array($options)) {
                    continue;
                }
                switch ($notification_name) {
                    case "freeMobile":
                        $key = "send_sms_free_mobile";
                        break;
                    case "ovh":
                        $key = "send_sms_ovh";
                        break;
                    default:
                        $key = "send_".$notification_name;
                }
                try {
                    $notifications[$key] = \Message\AdapterFactory::factory($notification_name, $options);
                    $this->_logger->debug(
                        "[Pid ".getmypid()."] USER : ".$user->getUsername().
                        " -> Notification ".get_class($notifications[$key])." activée"
                    );
                } catch (\Exception $e) {
                    $this->_logger->warn(
                        "[Pid ".getmypid()."] USER : ".$user->getUsername().
                        " -> Notification ".$notification_name." invalide"
                    );
                }
            }

            $alerts = $storage->fetchAll();
            $alerts_count = count($alerts);

            $this->_logger->info(sprintf(
                "[Pid %s] USER : %s -> %d alerte%4\$s trouvée%4\$s",
                getmypid(),
                $user->getUsername(),
                $alerts_count,
                $alerts_count > 1 ? "s" : ""
            ));

            if (0 == $alerts_count) {
                continue;
            }

            foreach ($alerts AS $i => $alert) {

                // Alerte suspendue
                if ($alert->suspend) {
                    continue;
                }

                $log_id = sprintf(
                    "[Pid %s] USER : %s - ALERT ID : %s -> ",
                    getmypid(),
                    $user->getUsername(),
                    $alert->id
                );

                try {
                    $config = SiteConfigFactory::factory($alert->url);
                } catch (Exception $e) {
                    $this->_logger->warn($log_id.$e->getMessage());
                    continue;
                }

                $unique_ads = $user->getOption("unique_ads");

                // Si le site ne fourni pas de date par annonce,
                // on est obligé de baser la dernière annonce reçue sur l'ID.
                if (!$config->getOption("has_date")) {
                    $unique_ads = true;
                }

                // Initialise l'heure de la dernière analyse
                if (!isset($alert->time_updated)) {
                    $alert->time_updated = 0;
                }

                // Réinitialise l'alerte si un renvoi de toutes les
                // annonces est demandé
                if ($reset) {
                    $alert->time_updated = 0;
                    $alert->last_id = array();
                    $alert->max_id = 0;
                    $alert->time_last_ad = 0;
                }

                $current_time = time();

                // Contrôle si l'alerte doit être analysée
                $next_time = (int) $alert->time_updated + $alert->interval * 60;
                if ($next_time > $current_time) {
                    continue;
                }

                $this->_logger->info($log_id."URL : ".$alert->url);

                try {
                    $parser = \AdService\ParserFactory::factory($alert->url);

                } catch (\AdService\Exception $e) {
                    $this->_logger->warn($log_id." ".$e->getMessage());
                    continue;
                }

                $this->_logger->debug($log_id."Dernière mise à jour : ".(
                    !empty($alert->time_updated) ?
                        date("d/m/Y H:i", (int) $alert->time_updated) :
                        "inconnue"
                ));
                $this->_logger->debug($log_id."Dernière annonce : ".(
                    !empty($alert->time_last_ad) ?
                        date("d/m/Y H:i", (int) $alert->time_last_ad) :
                        "inconnue"
                ));

                // Mise à jour de la date de dernière analyse
                $alert->time_updated = $current_time;


                // Récupération du résultat de recherche de l'alerte
                if (!$content = $this->_httpClient->request($alert->url)) {
                    $this->_logger->error($log_id."Curl Error : ".$this->_httpClient->getError());
                    continue;
                }

                // Configure un éventuel filtre sur les localités
                $cities = array();
                if ($alert->cities) {
                    $cities = array_map("trim", explode("\n", mb_strtolower($alert->cities)));
                }

                // Initialise le filtre d'annonce
                $filter = new \AdService\Filter(array(
                    "price_min" => $alert->price_min,
                    "price_max" => $alert->price_max,
                    "cities" => $cities,
                    "price_strict" => (bool)$alert->price_strict,
                    "categories" => $alert->getCategories(),
                    "min_id" => $unique_ads ? $alert->max_id : 0,
                    "exclude_ids" => $alert->last_id,
                ));

                // Parse le contenu pour récupérer les annonces
                // et applique le filtre
                $ads = $parser->process(
                    $content,
                    $filter,
                    parse_url($alert->url, PHP_URL_SCHEME)
                );

                $ads_count = count($ads);

                // Migre vers le nouveau système de détection d'annonce.
                if (is_numeric($alert->last_id)) {
                    $filter->setExcludeIds(array());
                    $alert->last_id = array();
                    $tmp_ads = $parser->process(
                        $content,
                        $filter,
                        parse_url($alert->url, PHP_URL_SCHEME)
                    );
                    foreach ($tmp_ads AS $tmp_ad) {
                        $alert->last_id[] = $tmp_ad->getId();
                    }
                    unset($tmp_ads, $tmp_ad);
                }

                // Si pas de nouvelle annonce à envoyer, on arrête là
                if ($ads_count == 0) {
                    $storage->save($alert);
                    continue;
                }

                $this->_logger->info(sprintf(
                    "%s%d annonce%3\$s trouvée%3\$s",
                    $log_id,
                    $ads_count,
                    $ads_count > 1 ? "s" : ""
                ));

                // Paramètres du site d'annonce
                $siteConfig = \AdService\SiteConfigFactory::factory($alert->url);

                foreach ($ads AS $ad) {
                    $time = $ad->getDate();
                    $id = $ad->getId();

                    // ID de l'annonce inconnu, on l'ajoute à la liste
                    if (!in_array($id, $alert->last_id)) {
                        array_unshift($alert->last_id, $id);
                    }

                    // On vérifie si la date de l'annonce est plus récente
                    // que la dernière date connue
                    if ($time && $alert->time_last_ad < $time) {
                        $alert->time_last_ad = $time;
                    }

                    // Si contrôle de nouvelle annonce basé sur l'ID,
                    // on vérifie si l'ID est plus récent
                    if ($unique_ads && $id > $alert->max_id) {
                        $alert->max_id = $id;
                    }
                }

                // On conserve 250 IDs d'annonce vues.
                if (250 < count($alert->last_id)) {
                    $alert->last_id = array_slice($alert->last_id, 0, 250);
                }

                $this->_mailer->clearAddresses();
                $error = false;

                if ($alert->send_mail) {
                    try {
                        $emails = explode(",", $alert->email);
                        foreach ($emails AS $email) {
                            $this->_mailer->addAddress(trim($email));
                        }
                    } catch (phpmailerException $e) {
                        $this->_logger->warn($log_id.$e->getMessage());
                        $error = true;
                    }
                    if (!$error) {
                        if ($alert->group_ads) {
                            $this->_mailer->Subject = sprintf(
                                "Alerte %s : %s",
                                $siteConfig->getOption("site_name"),
                                $alert->title
                            );
                            $this->_mailer->Body = require DOCUMENT_ROOT."/app/notifier/views/mail-ads.phtml";
                            $this->_mailer->AltBody = require DOCUMENT_ROOT."/app/notifier/views/mail-ads-text.phtml";
                            try {
                                $this->_mailer->send();
                            } catch (phpmailerException $e) {
                                $this->_logger->warn($log_id.$e->getMessage());
                            }

                        } else {
                            $ads = array_reverse($ads, true);
                            foreach ($ads AS $ad) {
                                $this->_mailer->Subject = ($alert->title?$alert->title." : ":"").$ad->getTitle();
                                $this->_mailer->Body = require DOCUMENT_ROOT."/app/notifier/views/mail-ad-single.phtml";
                                $this->_mailer->AltBody = require DOCUMENT_ROOT."/app/notifier/views/mail-ad-single-text.phtml";
                                try {
                                    $this->_mailer->send();
                                } catch (phpmailerException $e) {
                                    $this->_logger->warn($log_id.$e->getMessage());
                                }
                            }
                            $ads = array_reverse($ads, true);
                        }
                    }
                }

                if ($notifications) {
                    $messages = array();

                    // limite à 5 SMS par analyse de l'alerte (limite la
                    // consommation de crédit)
                    if ($ads_count <= 5) {
                        foreach ($ads AS $id => $ad) {
                            $ad = $ads[$id]; // récupère l'objet.
                            $url = $ad->getLink();
                            curl_setopt($curlTinyurl, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=".$url);
                            if ($url = curl_exec($curlTinyurl)) {
                                $others = array();

                                if ($ad->getPrice()) {
                                    $others[] = number_format($ad->getPrice(), 0, ',', ' ').$ad->getCurrency();
                                }

                                if ($ad->getCity()) {
                                    $others[] = $ad->getCity();

                                } elseif ($ad->getCountry()) {
                                    $others[] = $ad->getCountry();
                                }

                                $others = implode(", ", $others);

                                $messages[] = array(
                                    "title" => "Alerte ".$siteConfig->getOption("site_name"),
                                    "description" => sprintf(
                                        "Nouvelle annonce%s",
                                        $alert->title ? " pour : ".$alert->title : ""
                                    ),
                                    "url" => $url,
                                    "text" => sprintf(
                                        "Annonce %s%s%s",
                                        $alert->title ? $alert->title." : " : "",
                                        $ad->getTitle(),
                                        $others ? " (".$others.")" : ""
                                    ),
                                );
                            }
                        }

                    // Si plus de 5 nouvelles annonces, on envoie un
                    // message global
                    } else {
                        curl_setopt($curlTinyurl, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=".$alert->url);
                        if ($url = curl_exec($curlTinyurl)) {
                            $messages[] = array(
                                "title" => "Alerte ".$siteConfig->getOption("site_name"),
                                "url" => $url,
                                "description" => sprintf(
                                    "Nouvelles annonces%s",
                                    $alert->title ? " pour : ".$alert->title : ""
                                ),
                                "text" => sprintf(
                                    "Il y a %d nouvelles annonces pour votre alerte '%s'",
                                    $ads_count,
                                    $alert->title ? $alert->title : "sans nom"
                                ),
                            );
                        }
                    }

                    // Envoi des messages via les systèmes de notification choisis
                    foreach ($messages AS $message) {
                        foreach ($notifications AS $key => $notifier) {
                            if (empty($alert->$key)) {
                                continue;
                            }

                            try {
                                $text = $message["text"];
                                unset($message["text"]);
                                $notifier->send($text, $message);
                            } catch (Exception $e) {
                                $this->_logger->warn(
                                    $log_id."Erreur sur envoi via ".
                                    get_class($notifier).
                                    ": (".$e->getCode().") ".
                                    $e->getMessage()
                                );
                            }
                        }
                    }
                }

                $storage->save($alert);
            }
        }

        curl_close($curlTinyurl);
        $this->_mailer->smtpClose();
    }

    public function shutdown()
    {
        if ($this->_running && is_file($this->_lockFile)) {
            unlink($this->_lockFile);
        }
    }

    public function sigHandler($no)
    {
        if (in_array($no, array(SIGTERM, SIGINT))) {
            $this->_logger->info("[Pid ".getmypid()."] QUIT (".$no.")");
            $this->shutdown();
            exit;
        }
    }

    protected function _checkConnection()
    {
        // teste la connexion
        $this->_httpClient->setDownloadBody(false);
        if (false === $this->_httpClient->request("https://www.leboncoin.fr")) {
            throw new Exception("Connexion vers https://www.leboncoin.fr échouée".
                (($error = $this->_httpClient->getError())?" (erreur: ".$error.")":"").".");
        }
        if (200 != $code = $this->_httpClient->getRespondCode()) {
            throw new Exception("Code HTTP différent de 200 : ".$code);
        }
        $this->_httpClient->setDownloadBody(true);
    }

    protected function _lock()
    {
        if (is_file($this->_lockFile)) {
            throw new Exception("Un processus est en cours d'exécution.");
        }
        file_put_contents($this->_lockFile, time()."\n".getmypid());
        return $this;
    }
}

require __DIR__."/../../../bootstrap.php";

// lib
require_once "PHPMailer/class.phpmailer.php";

// modèle
$storageType = $config->get("storage", "type", "files");
if ($storageType == "db") {
    $userStorage = new \App\Storage\Db\User($dbConnection);
} else {
    $userStorage = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
}

if (is_file(DOCUMENT_ROOT."/var/.lock_update")) {
    Logger::getLogger("main")->info("Tâche annulée : une mise à jour de l'application est en cours.");
    return;
}

try {
    $main = new Main($config, $client, $userStorage);
} catch (\Exception $e) {
    Logger::getLogger("main")->info($e->getMessage());
    return;
}

$main->check();
$main->shutdown();





