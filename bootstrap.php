<?php

define("DOCUMENT_ROOT", __DIR__);
define("DS", DIRECTORY_SEPARATOR);

// Define application environment
defined("APPLICATION_ENV")
    || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));

// Define application environment
define("APPLICATION_VERSION", require DOCUMENT_ROOT."/version.php");

// Constante utilisée pour forcer le navigateur à télécharger
// les fichiers statiques (js, css, html, etc)
define("STATIC_REV", 14);

define("COOKIE_PATH", DOCUMENT_ROOT."/var/tmp");

set_include_path(
    __DIR__."/lib".PATH_SEPARATOR.get_include_path()
);

require_once "Http/Client/Curl.php";
require_once "Config/Lite.php";
require_once "Log4php/Logger.php";

class Application
{
    /**
     * @var Config_Lite
     */
    protected $_config;

    /**
     * @var HttpClientCurl
     */
    protected $_connectors = array();

    protected function initAutoload()
    {
        spl_autoload_register(function ($className) {
            $filename = ltrim(str_replace("\\", "/", $className), "/");
            if (false !== strpos($filename, "App/")) {
                $filename = __DIR__."/app/models/".
                    str_replace("App/", "", $filename).".php";
            } else {
                $filename = __DIR__."/lib/".$filename.".php";
            }
            if (is_file($filename)) {
                require_once $filename;
            }
        });
    }

    protected function initPHPConfig()
    {
        mb_internal_encoding("UTF-8");
        if (function_exists("date_default_timezone_set")) {
            date_default_timezone_set("Europe/Paris");
        }
        if (APPLICATION_ENV == "development") {
            ini_set("display_errors", true);
            ini_set("error_reporting", -1);
        }
    }

    protected function initLogger()
    {
        Logger::configure(array(
            "rootLogger" => array(
                "appenders" => array("default", "error"),
                "level" => APPLICATION_ENV == "development"?"debug":"info"
            ),
            "appenders" => array(
                "default" => array(
                    "class" => "LoggerAppenderRollingFile",
                    "layout" => array(
                        "class" => "LoggerLayoutPattern",
                        "params" => array(
                            "conversionPattern" => "%date %-5level %msg%n"
                        )
                    ),
                    "params" => array(
                        "file" => DOCUMENT_ROOT."/var/log/info.log",
                        "maxFileSize" => APPLICATION_ENV == "development"?"20MB":"3MB",
                        "maxBackupIndex" => 5,
                        "append" => true,
                        "threshold" => "all",
                    )
                ),
                "error" => array(
                    "class" => "LoggerAppenderRollingFile",
                    "layout" => array(
                        "class" => "LoggerLayoutPattern",
                        "params" => array(
                            "conversionPattern" => "%date %-5level %msg%n"
                        )
                    ),
                    "params" => array(
                        "file" => DOCUMENT_ROOT."/var/log/error.log",
                        "maxFileSize" => "3MB",
                        "maxBackupIndex" => 5,
                        "append" => true,
                        "threshold" => "error",
                    )
                )
            )
        ));
    }

    protected function initConfig()
    {
        // valeurs par défaut.
        $this->_config->set("proxy", "ip", "");
        $this->_config->set("proxy", "port", "");
        $this->_config->set("proxy", "user", "");
        $this->_config->set("proxy", "password", "");
        $this->_config->set("http", "user_agent", "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.6) Gecko/20100628 Ubuntu/10.04 (lucid) Firefox/3.6.6");
        $this->_config->set("general", "check_start", 7);
        $this->_config->set("general", "check_end", 24);
        $this->_config->set("general", "version", 0);
        $this->_config->set("general", "baseurl", "");
        $this->_config->set("storage", "type", "files");

        // lit la configuration du fichier.
        try {
            $this->_config->read();
        } catch (Config_Lite_Exception_Runtime $e) {
            return;
        }

        $baseurl_locked = $this->_config->get("general", "baseurl_locked", 0);
        if (!$baseurl_locked && isset($_SERVER["HTTP_HOST"])) {
            $current_base_url = $this->_config->get("general", "baseurl", "");
            $base_url = "http";
            if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") {
                $base_url .= "s";
            }
            $base_url .= "://".$_SERVER["HTTP_HOST"]."/";
            if (!empty($_SERVER["REQUEST_URI"])) {
                $request_uri = trim($_SERVER["REQUEST_URI"], "/");
                if (false !== $pos = strpos($request_uri, "?")) {
                    $request_uri = mb_substr($request_uri, 0, $pos);
                }
                if (false !== strpos($request_uri, ".php")) {
                    $request_uri = substr($request_uri, 0, strrpos($request_uri, "/"));
                }
                if ($request_uri) {
                    $base_url .= trim($request_uri, "/")."/";
                }
            }
            if ($base_url != $current_base_url) {
                $this->_config->set("general", "baseurl", $base_url);
                $this->_config->save();
            }
        }
    }

    /**
     *
     * @param string $url
     * @return HttpClientCurl
     */
    public function getConnector($url)
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!isset($this->_connectors[$host])) {
            // Pour Leboncoin, limite le nombre de requête par seconde
            if (false !== strpos($host, "leboncoin")) {
                $connector = new App\Lbc\HTTPConnector();
                $connector->setHeader("Accept", "*/*")
                          ->setHeader("api_key", $this->_config->get("lbc", "api_key", "ba0c2dad52b3ec"))
                          ->setHeader("origin", "https://www.leboncoin.fr")
                          ->setHeader("content-type", "text/plain;charset=UTF-8");

            } else {
                $connector = new HttpClientCurl();
            }

            $proxy = $this->_config->get("proxy", null, array());
            if (!empty($proxy["ip"])) {
                $connector->setProxyIp($proxy["ip"]);
                if (!empty($proxy["port"])) {
                    $connector->setProxyPort($proxy["port"]);
                }
            }

            if ($userAgent = $this->_config->get("http", "user_agent", "")) {
                $connector->setUserAgent($userAgent);
            }

            $connector->setCookiePath(COOKIE_PATH);

            $this->_connectors[$host] = $connector;
        }

        $this->_connectors[$host]->setUrl($url);

        return $this->_connectors[$host];
    }

    public function __construct()
    {
        set_exception_handler(array($this, "_exceptionHandler"));
        set_error_handler(
            array($this, "_errorHandler"),
            E_ERROR | E_WARNING | E_USER_ERROR | E_USER_WARNING
        );
    }

    public function bootstrap(Config_Lite $config)
    {
        $this->_config = $config;
        foreach (get_class_methods($this) AS $method) {
            if (0 === strpos($method, "init")) {
                $this->$method();
            }
        }
    }

    /**
     * @return boolean|string
     */
    public function upgradeAvailable()
    {
        if (!$currentVersion = $this->_config->get("general", "version", "")) {
            return false;
        }

        $updater = new \App\Updater(APPLICATION_VERSION);

        try {
            $latestVersion = $updater->getLatestVersion();

        } catch (Exception $e) {
            return false;
        }

        return version_compare($currentVersion, $latestVersion, "<") ?
               $latestVersion : false;
    }

    public function _exceptionHandler($e)
    {
        Logger::getLogger("main")->error(
            get_class($e)." : #".
                $e->getCode()." ".
                $e->getMessage()." (".$e->getFile().":".$e->getLine().")"
        );

        if ("development" == APPLICATION_ENV) {
            var_dump($e);
            return;
        }

        die("Un problème est survenu lors de l'exécution du programme.\n");
    }

    public function _errorHandler($errno, $errstr, $errfile, $errline)
    {
        $display_errors = ini_get("display_errors");
        if ($display_errors || "development" == APPLICATION_ENV) {
            var_dump($display_errors,$errno, $errstr, $errfile, $errline);
            return false;
        }

        switch ($errno) {
            case E_NOTICE:
                $errno_const = "E_NOTICE";
                break;
            case E_ERROR:
                $errno_const = "E_ERROR";
                break;
            case E_USER_ERROR:
                $errno_const = "E_USER_ERROR";
                break;
            case E_WARNING:
                $errno_const = "E_WARNING";
                break;
            case E_USER_WARNING:
                $errno_const = "E_USER_WARNING";
                break;
            default:
                $errno_const = $errno;
        }

        Logger::getLogger("main")->error(
            $errno_const." ".$errstr." (".$errfile.":".$errline.")"
        );

        // Pour les ERROR, on stoppe l'exécution du script
        if ($errno == E_ERROR || $errno == E_USER_ERROR) {
            die("Un problème est survenu lors de l'exécution du programme.\n");
        }

        return true;
    }
}

$config = new Config_Lite(DOCUMENT_ROOT."/var/config.ini");

$app = new Application();
$app->bootstrap($config);
$userAuthed = null;

// si stockage en base de données, on initialise la connexion
if ("db" == $config->get("storage", "type", "files")) {
    $options = array_merge(array(
        "host" => "",
        "user" => "",
        "password" => "",
        "dbname" => ""
    ), $config->get("storage", "options"));
    $dbConnection = new mysqli(
        $options["host"],
        $options["user"],
        $options["password"],
        $options["dbname"]
    );
    if ($dbConnection->connect_error) {
        Logger::getLogger("main")->error(
            "Connexion à la base de données échouée : ".
            $dbConnection->connect_error
        );
        echo "Un problème est survenu lors de la génération de la page.";
        exit;
    }
    $driver = new mysqli_driver();
    $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
    $dbConnection->set_charset("utf8");
    unset($options);
}
