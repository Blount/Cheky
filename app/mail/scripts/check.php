<?php

set_time_limit(0);

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

        $this->_logger->info("Vérification des alertes.");

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
            $this->_logger->info("Hors de la plage horaire. Contrôle annulé.");
            return;
        }
        $this->_logger->info("Contrôle des alertes.");
        $this->_checkConnection();
        $users = $this->_userStorage->fetchAll();

        // génération d'URL court pour les SMS
        $curlTinyurl = curl_init();
        curl_setopt($curlTinyurl, CURLOPT_RETURNTRANSFER, 1);

        $storageType = $this->_config->get("storage", "type", "files");

        foreach ($users AS $user) {
            if ($storageType == "db") {
                $storage = new \App\Storage\Db\Alert($this->_userStorage->getDbConnection(), $user);
                $this->_logger->info("User: ".$user->getUsername());
            } else {
                $file = DOCUMENT_ROOT."/var/configs/".$user->getUsername().".csv";
                if (!is_file($file)) {
                    continue;
                }
                $storage = new \App\Storage\File\Alert($file);
                $this->_logger->debug("Fichier config: ".$file);
            }

            // configuration des notifications.
            $notifications = array();
            $notifications_params = $user->getOption("notification");
            if ($notifications_params && is_array($notifications_params)) {
                foreach ($notifications_params AS $notification_name => $options) {
                    try {
                        $notifications[$notification_name] = \Message\AdapterFactory::factory($notification_name, $options);
                        $this->_logger->debug("notification ".get_class($notifications[$notification_name])." activée");
                    } catch (\Exception $e) {
                        $this->_logger->warn("notification ".$notification_name." invalide");
                    }
                }
            }

            $alerts = $storage->fetchAll();
            $this->_logger->info(count($alerts)." alerte".
                (count($alerts) > 1?"s":"")." trouvée".(count($alerts) > 1?"s":""));
            if (count($alerts) == 0) {
                continue;
            }
            $unique_ads = $user->getOption("unique_ads");
            foreach ($alerts AS $i => $alert) {
                $currentTime = time();
                if (!isset($alert->time_updated)) {
                    $alert->time_updated = 0;
                }
                if (((int)$alert->time_updated + (int)$alert->interval*60) > $currentTime
                    || $alert->suspend) {
                    continue;
                }
                $this->_logger->info("Contrôle de l'alerte ".$alert->url);
                try {
                    $parser = \AdService\ParserFactory::factory($alert->url);
                } catch (\AdService\Exception $e) {
                    $this->_logger->info("\t".$e->getMessage());
                    continue;
                }

                $this->_logger->debug("Dernière mise à jour : ".(!empty($alert->time_updated)?date("d/m/Y H:i", (int)$alert->time_updated):"inconnue"));
                $this->_logger->debug("Dernière annonce : ".(!empty($alert->time_last_ad)?date("d/m/Y H:i", (int)$alert->time_last_ad):"inconnue"));
                $alert->time_updated = $currentTime;
                if (!$content = $this->_httpClient->request($alert->url)) {
                    $this->_logger->error("Curl Error : ".$this->_httpClient->getError());
                    continue;
                }
                $cities = array();
                if ($alert->cities) {
                    $cities = array_map("trim", explode("\n", mb_strtolower($alert->cities)));
                }
                $filter = new \AdService\Filter(array(
                    "price_min" => $alert->price_min,
                    "price_max" => $alert->price_max,
                    "cities" => $cities,
                    "price_strict" => (bool)$alert->price_strict,
                    "categories" => $alert->getCategories(),
                    "min_id" => $unique_ads ? $alert->last_id : 0
                ));
                $ads = $parser->process($content, $filter);
                $countAds = count($ads);
                if ($countAds == 0) {
                    $storage->save($alert);
                    continue;
                }
                $siteConfig = \AdService\SiteConfigFactory::factory($alert->url);
                $newAds = array();
                $time_last_ad = (int)$alert->time_last_ad;
                foreach ($ads AS $ad) {
                    if ($time_last_ad < $ad->getDate()) {
                        $newAds[$ad->getId()] = require DOCUMENT_ROOT."/app/mail/views/mail-ad.phtml";
                        if ($alert->time_last_ad < $ad->getDate()) {
                            $alert->time_last_ad = $ad->getDate();
                        }
                        if ($unique_ads && $ad->getId() > $alert->last_id) {
                            $alert->last_id = $ad->getId();
                        }
                    }
                }
                if (!$newAds) {
                    $storage->save($alert);
                    continue;
                }
                $countAds = count($newAds);
                $this->_logger->info($countAds." annonce".
                    ($countAds > 1?"s":"")." trouvée".($countAds > 1?"s":""));
                $this->_mailer->clearAddresses();
                $error = false;
                if ($alert->send_mail) {
                    try {
                        $emails = explode(",", $alert->email);
                        foreach ($emails AS $email) {
                            $this->_mailer->addAddress(trim($email));
                        }
                    } catch (phpmailerException $e) {
                        $this->_logger->warn($e->getMessage());
                        $error = true;
                    }
                    if (!$error) {
                        if ($alert->group_ads) {
                            $newAdsCount = count($newAds);
                            $subject = "Alert ".$siteConfig->getOption("site_name")." : ".$alert->title;
                            $message = '<h2>'.$newAdsCount.' nouvelle'.($newAdsCount > 1?'s':'').' annonce'.($newAdsCount > 1?'s':'').' - '.date("d/m/Y H:i", $currentTime).'</h2>
                            <p>Lien de recherche: <a href="'.htmlspecialchars($alert->url, null, "UTF-8").'">'.htmlspecialchars($alert->url, null, "UTF-8").'</a></p>
                            <hr /><br />'.
                            implode("<br /><hr /><br />", $newAds).'<hr /><br />';

                            $this->_mailer->Subject = $subject;
                            $this->_mailer->Body = $message;
                            try {
                                $this->_mailer->send();
                            } catch (phpmailerException $e) {
                                $this->_logger->warn($e->getMessage());
                            }
                        } else {
                            $newAds = array_reverse($newAds, true);
                            foreach ($newAds AS $id => $ad) {
                                $subject = ($alert->title?$alert->title." : ":"").$ads[$id]->getTitle();
                                $message = '<h2>Nouvelle annonce - '.date("d/m/Y H:i", $currentTime).'</h2>
                                <p>Lien de recherche: <a href="'.htmlspecialchars($alert->url, null, "UTF-8").'">'.htmlspecialchars($alert->url, null, "UTF-8").'</a></p>
                                <hr /><br />'.$ad.'<hr /><br />';

                                $this->_mailer->Subject = $subject;
                                $this->_mailer->Body = $message;
                                try {
                                    $this->_mailer->send();
                                } catch (phpmailerException $e) {
                                    $this->_logger->warn($e->getMessage());
                                }
                            }
                        }
                    }
                }
                if ($notifications && (
                       $alert->send_sms_free_mobile
                    || $alert->send_sms_ovh
                    || $alert->send_pushbullet
                    || $alert->send_notifymyandroid
                    || $alert->send_pushover
                )) {
                    if ($countAds < 5) { // limite à 5 SMS
                        foreach ($newAds AS $id => $ad) {
                            $ad = $ads[$id]; // récupère l'objet.
                            $url = $ad->getLink();
                            if (false !== strpos($url, "leboncoin")) {
                                $url = "http://mobile.leboncoin.fr/vi/".$ad->getId().".htm";
                            }
                            curl_setopt($curlTinyurl, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=".$url);
                            if ($url = curl_exec($curlTinyurl)) {
                                $msg  = "Nouvelle annonce ".($alert->title?$alert->title." : ":"").$ad->getTitle();
                                $others = array();
                                if ($ad->getPrice()) {
                                    $others[] = number_format($ad->getPrice(), 0, ',', ' ').$ad->getCurrency();
                                }
                                if ($ad->getCity()) {
                                    $others[] = $ad->getCity();
                                } elseif ($ad->getCountry()) {
                                    $others[] = $ad->getCountry();
                                }
                                if ($others) {
                                    $msg .= " (".implode(", ", $others).")";
                                }
                                $params = array(
                                    "title" => "Alerte ".$siteConfig->getOption("site_name"),
                                    "description" => "Nouvelle annonce".($alert->title ? " pour : ".$alert->title : ""),
                                    "url" => $url,
                                );
                                foreach ($notifications AS $key => $notifier) {
                                    switch ($key) {
                                        case "freeMobile":
                                            $key_test = "send_sms_free_mobile";
                                            break;
                                        case "ovh":
                                            $key_test = "send_sms_ovh";
                                            break;
                                        default:
                                            $key_test = "send_".$key;
                                    }
                                    if (isset($alert->$key_test) && $alert->$key_test) {
                                        try {
                                            $notifier->send($msg, $params);
                                        } catch (Exception $e) {
                                            $this->_logger->warn("Erreur sur envoi via ".get_class($notifier).": (".$e->getCode().") ".$e->getMessage());
                                        }
                                    }
                                }
                            }
                        }
                    } else { // envoi un msg global
                        curl_setopt($curlTinyurl, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=".$alert->url);
                        if ($url = curl_exec($curlTinyurl)) {
                            $msg  = "Il y a ".$countAds." nouvelles annonces pour votre alerte '".($alert->title?$alert->title:"sans nom")."'";
                            $params = array(
                                "title" => "Alerte ".$siteConfig->getOption("site_name"),
                                "description" => "Nouvelle".($countAds > 1 ? "s" : "").
                                            " annonce".($countAds > 1 ? "s" : "").
                                            ($alert->title ? " pour : ".$alert->title : ""),
                                "url" => $url,
                            );
                            foreach ($notifications AS $key => $notifier) {
                                switch ($key) {
                                    case "freeMobile":
                                        $key_test = "send_sms_free_mobile";
                                        break;
                                    case "ovh":
                                        $key_test = "send_sms_ovh";
                                        break;
                                    default:
                                        $key_test = "send_".$key;
                                }
                                if (isset($alert->$key_test) && $alert->$key_test) {
                                    try {
                                        $notifier->send($msg, $params);
                                    } catch (Exception $e) {
                                        $this->_logger->warn("Erreur sur envoi via ".get_class($notifier).": (".$e->getCode().") ".$e->getMessage());
                                    }
                                }
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
            $this->_logger->info("QUIT (".$no.")");
            $this->shutdown();
            exit;
        }
    }

    protected function _checkConnection()
    {
        // teste la connexion
        $this->_httpClient->setDownloadBody(false);
        if (false === $this->_httpClient->request("http://www.leboncoin.fr")) {
            throw new Exception("Connexion vers http://www.leboncoin.fr échouée".
                (($error = $this->_httpClient->getError())?" (erreur: ".$error.")":"").".");
        }
        if (200 != $this->_httpClient->getRespondCode()) {
            throw new Exception("Code HTTP différent de 200.");
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

try {
    $main = new Main($config, $client, $userStorage);
} catch (\Exception $e) {
    Logger::getLogger("main")->info($e->getMessage());
    return;
}

try {
    $main->check();
} catch (\Exception $e) {
    Logger::getLogger("main")->warn($e->getMessage());
}
$main->shutdown();





