<?php

set_time_limit(0);
$key = "";

if ($key && (!isset($_GET["key"]) || $_GET["key"] != $key)) {
    return;
}

declare(ticks = 1);

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
     * @var \Lbc\Parser
     */
    protected $_parser;

    /**
     * @var PHPMailer
     */
    protected $_mailer;

    /**
     * @var App\User\Storage
     */
    protected $_userStorage;

    protected $_lockFile;
    protected $_loop;
    protected $_sleeping;
    protected $_logger;
    protected $_timer = 5;
    protected $_countError = 0;

    public function __construct(
        \Config_Lite $config,
        \HttpClientAbstract $client,
        \App\Storage\User $userStorage)
    {
        $this->_config = $config;

        $this->_loop = true;
        $this->_sleeping = false;

        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, array($this, "sigHandler"));
            pcntl_signal(SIGINT, array($this, "sigHandler"));
        }

        $this->_httpClient = $client;
        $this->_userStorage = $userStorage;

        $this->_lockFile = DOCUMENT_ROOT."/var/.lock";
        $this->_lock();

        $this->_logger = Logger::getLogger("main");
        $this->_logger->info("Démon démarré");

        $this->_parser = new \Lbc\Parser();

        $this->_mailer = new PHPMailer($exceptions=true);
        $this->_mailer->setLanguage("fr", DOCUMENT_ROOT."/lib/PHPMailer/language/");
        $this->_mailer->CharSet = "utf-8";
        if ($config->hasSection("mailer")) {
            if ($smtp = $config->get("mailer", "smtp", array())) {
                $this->_mailer->SMTPKeepAlive = true;
                $this->_mailer->isSMTP();
                if (!empty($smtp["host"])) {
                    $this->_mailer->Host = $smtp["host"];
                }
                if (!empty($smtp["port"])) {
                    $this->_mailer->Port = $smtp["port"];
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

    public function loop()
    {
        $this->_loop = true;
        $this->_sleeping = false;
        while ($this->_loop) {
            $this->check();
            $this->_logger->info("Prochain contrôle dans ".$this->_timer." minute".($this->_timer > 1?"s":""));
            if ($this->_loop) {
                $this->_sleeping = true;
                sleep($this->_timer * 60);
                $this->_sleeping = false;
            }
        }
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
        try {
            $this->_checkConnection();
        } catch (Exception $e) {
            if ($this->_countError < 6) {
                $this->_countError++;
            }
            $this->_timer = $this->_countError * 10;
            $this->_logger->warn($e->getMessage());
            return;
        }
        $this->_countError = 0;
        $this->_timer = 5;
        $users = $this->_userStorage->fetchAll();

        // génération d'URL court pour les SMS
        $curlTinyurl = curl_init();
        curl_setopt($curlTinyurl, CURLOPT_RETURNTRANSFER, 1);

        // pour envoi de SMS
        $sms = new \SMS\FreeMobile();

        $storageType = $this->_config->get("storage", "type");

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
                $this->_logger->info("Fichier config: ".$file);
            }

            // configuration SMS
            $sms->setKey($user->getOption("free_mobile_key"))
                ->setUser($user->getOption("free_mobile_user"));

            $alerts = $storage->fetchAll();
            $this->_logger->info(count($alerts)." alerte".
                (count($alerts) > 1?"s":"")." trouvée".(count($alerts) > 1?"s":""));
            if (count($alerts) == 0) {
                continue;
            }
            foreach ($alerts AS $i => $alert) {
                $currentTime = time();
                if (!isset($alert->time_updated)) {
                    $alert->time_updated = 0;
                }
                $this->_logger->info("Contrôle de l'alerte ".$alert->url);
                $this->_logger->debug("Dernière mise à jour : ".(!empty($alert->time_updated)?date("d/m/Y H:i", (int)$alert->time_updated):"inconnue"));
                $this->_logger->debug("Dernière annonce : ".(!empty($alert->time_last_ad)?date("d/m/Y H:i", (int)$alert->time_last_ad):"inconnue"));
                if (((int)$alert->time_updated + (int)$alert->interval*60) > $currentTime
                    || $alert->suspend) {
                    continue;
                }
                $alert->time_updated = $currentTime;
                if (!$content = $this->_httpClient->request($alert->url)) {
                    $this->_logger->error("Curl Error : ".$this->_httpClient->getError());
                    continue;
                }
                $ads = $this->_parser->process($content, array(
                    "price_min" => $alert->price_min,
                    "price_max" => $alert->price_max,
                    "cities" => $alert->cities,
                    "price_strict" => (bool)$alert->price_strict,
                    "categories" => $alert->getCategories()
                ));
                $countAds = count($ads);
                if ($countAds == 0) {
                    $storage->save($alert);
                    continue;
                }
                $newAds = array();
                $time_last_ad = (int)$alert->time_last_ad;
                foreach ($ads AS $ad) {
                    if ($time_last_ad < $ad->getDate()) {
                        $newAds[$ad->getId()] = require DOCUMENT_ROOT."/app/mail/views/mail-ad.phtml";
                        if ($alert->time_last_ad < $ad->getDate()) {
                            $alert->time_last_ad = $ad->getDate();
                        }
                    }
                }
                if ($newAds) {
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
                                $subject = "Alert LeBonCoin : ".$alert->title;
                                $message = '<h2>Alerte générée le '.date("d/m/Y H:i", $currentTime).'</h2>
                                <p>Lien de recherche: <a href="'.htmlspecialchars($alert->url, null, "UTF-8").'">'.htmlspecialchars($alert->url, null, "UTF-8").'</a></p>
                                <p>Liste des nouvelles annonces :</p><hr /><br />'.
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
                                    $message = '<h2>Alerte générée le '.date("d/m/Y H:i", $currentTime).'</h2>
                                    <p>Lien de recherche: <a href="'.htmlspecialchars($alert->url, null, "UTF-8").'">'.htmlspecialchars($alert->url, null, "UTF-8").'</a></p>
                                    <p>Nouvelle annonce :</p><hr /><br />'.$ad.'<hr /><br />';

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
                    if ($alert->send_sms) {
                        if ($countAds < 5) { // limite à 5 SMS
                            foreach ($newAds AS $id => $ad) {
                                $ad = $ads[$id]; // récupère l'objet.'
                                $url = "http://mobile.leboncoin.fr/vi/".$ad->getId().".htm";
                                curl_setopt($curlTinyurl, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=".$url);
                                if ($url = curl_exec($curlTinyurl)) {
                                    $msg  = "Nouvelle annonce ".($alert->title?$alert->title." : ":"").$ad->getTitle();
                                    $others = array();
                                    if ($ad->getPrice()) {
                                        $others[] = number_format($ad->getPrice(), 0, ',', ' ')."€";
                                    }
                                    if ($ad->getCity()) {
                                        $others[] = $ad->getCity();
                                    } elseif ($ad->getCountry()) {
                                        $others[] = $ad->getCountry();
                                    }
                                    if ($others) {
                                        $msg .= " (".implode(", ", $others).")";
                                    }
                                    $msg .= ": ".$url;
                                    try {
                                        $sms->send($msg);
                                    } catch (Exception $e) {
                                        $this->_logger->warn("Erreur sur envoi de SMS: (".$e->getCode().") ".$e->getMessage());
                                    }
                                }
                            }
                        } else { // envoi un msg global
                            curl_setopt($curlTinyurl, CURLOPT_URL, "http://tinyurl.com/api-create.php?url=".$alert->url);
                            if ($url = curl_exec($curlTinyurl)) {
                                $msg  = "Il y a ".$countAds." nouvelles annonces pour votre alerte '".($alert->title?$alert->title:"sans nom")."'";
                                $msg .= ": ".$url;
                                try {
                                    $sms->send($msg);
                                } catch (Exception $e) {
                                    $this->_logger->warn("Erreur sur envoi de SMS: (".$e->getCode().") ".$e->getMessage());
                                }
                            }
                        }
                    }
                }
                $storage->save($alert);
            }
        }

        unset($sms);
        curl_close($curlTinyurl);
        $this->_mailer->smtpClose();
    }

    public function shutdown()
    {
        $this->_loop = false;
        if (is_file($this->_lockFile)) {
            unlink($this->_lockFile);
        }
    }

    public function sigHandler($no)
    {
        if (in_array($no, array(SIGTERM, SIGINT))) {
            $this->_logger->info("QUIT (".$no.")");
            $this->shutdown();
            if (!$this->_sleeping) {
                exit;
            }
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
            $currentTime = (int) file_get_contents($this->_lockFile);
            if ((time() - $currentTime) < (10 * 60)) {
                throw new Exception("Impossible de lancer le contrôle des alertes.");
            }
        }
        file_put_contents($this->_lockFile, time());
        return $this;
    }
}

require dirname(__FILE__)."/../../../bootstrap.php";

// lib
require_once "PHPMailer/class.phpmailer.php";
require_once "SMS/FreeMobile.php";

// modèle
$storageType = $config->get("storage", "type");
if ($storageType == "db") {
    require_once DOCUMENT_ROOT."/app/models/Storage/Db/Alert.php";
    require_once DOCUMENT_ROOT."/app/models/Storage/Db/User.php";
    $userStorage = new \App\Storage\Db\User($dbConnection);
} else {
    require_once DOCUMENT_ROOT."/app/models/Storage/File/Alert.php";
    require_once DOCUMENT_ROOT."/app/models/Storage/File/User.php";
    $userStorage = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
}

$daemon = isset($_SERVER["argv"]) && is_array($_SERVER["argv"])
    && in_array("--daemon", $_SERVER["argv"]);

$main = new Main($config, $client, $userStorage);

if (!$daemon) {
    $main->check();
    $main->shutdown();
    return;
}
$main->loop();
$main->shutdown();





