<?php

define("DOCUMENT_ROOT", __DIR__);
define("DS", DIRECTORY_SEPARATOR);

// Define application environment
defined("APPLICATION_ENV")
    || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));

// Define application environment
define("APPLICATION_VERSION", require DOCUMENT_ROOT."/version.php");

set_include_path(
    __DIR__."/lib".PATH_SEPARATOR.get_include_path()
);

require_once "Http/Client/Curl.php";
require_once "Config/Lite.php";
require_once "Log4php/Logger.php";

class Bootstrap
{
    /**
     * @var Config_Lite
     */
    protected $_config;

    /**
     * @var HttpClientCurl
     */
    protected $_client;

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
                "appenders" => array("default"),
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
                        "maxFileSize" => "3MB",
                        "maxBackupIndex" => 5,
                        "append" => true
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

        if (isset($_SERVER["HTTP_HOST"])) {
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

    public function getClient()
    {
        if (!$this->_client) {
            $client = new HttpClientCurl();
            $proxy = $this->_config->get("proxy", null, array());
            if (!empty($proxy["ip"])) {
                $client->setProxyIp($proxy["ip"]);
                if (!empty($proxy["port"])) {
                    $client->setProxyPort($proxy["port"]);
                }
            }
            if ($userAgent = $this->_config->get("http", "user_agent", "")) {
                $client->setUserAgent($userAgent);
            }
        }
        return $client;
    }

    public function __construct()
    {
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
}

$config = new Config_Lite(DOCUMENT_ROOT."/var/config.ini");

$bootstrap = new Bootstrap();
$bootstrap->bootstrap($config);
$userAuthed = null;

// initialise le client HTTP.
$client = $bootstrap->getClient();

// si stockage en base de données, on initialise la connexion
if ("db" == $config->get("storage", "type", "files")) {
    $options = array_merge(array(
        "host" => "",
        "user" => "",
        "password" => "",
        "dbname" => ""
    ), $config->get("storage", "options"));
    $dbConnection = new mysqli($options["host"], $options["user"],
        $options["password"], $options["dbname"]);
    unset($options);
}
