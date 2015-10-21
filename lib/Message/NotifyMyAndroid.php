<?php

namespace Message;

require_once __DIR__."/Abstract.php";

class NotifyMyAndroid extends Message_Abstract
{
    protected $_verify_url = "https://www.notifymyandroid.com/publicapi/verify";
    protected $_notify_url = "https://www.notifymyandroid.com/publicapi/notify";

    /**
     * @var string
     */
    protected $_token;

    protected $_curl;

    public function __construct(array $options = array())
    {
        $this->_curl = curl_init();
        parent::__construct($options);
    }

    public function __destruct()
    {
        curl_close($this->_curl);
    }

    /**
     * Envoi un message.
     * @param string $msg
     * @throws \Exception
     */
    public function send($message, array $options = array())
    {
        if (!$this->_token || empty($options["event"])) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        $content = array(
            "apikey" => $this->getToken(),
            "application" => isset($options["application"]) ?
                $options["application"] : "Alerte",
            "event" => $options["event"],
            "description" => $message,
        );
        if (isset($options["url"])) {
            $content["url"] = $options["url"];
        }

        curl_setopt($this->_curl, CURLOPT_URL, $this->_notify_url);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_HEADER, false);
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, http_build_query($content));
//         curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $content);

        $response = curl_exec($this->_curl);

        if ($response === false) {
            throw new \Exception("cURL Error: " . curl_error($this->_curl));
        }

        $code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        if ($code == 200) {
            // find error through content

        }
        if ($code != 200) {
            switch ($code) {
                case 400:
                    $msg = "The data supplied is in the wrong format, invalid length or null.";
                    break;
                case 401:
                    $msg = "None of the API keys provided were valid.";
                    break;
                case 402:
                    $msg = "Maximum number of API calls per hour exceeded.";
                    break;
                case 500:
                    $msg = "Internal server error. Please contact our support if the problem persists.";
                    break;
                default:
                    $msg = "Unknow error.";
            }
            throw new \Exception($msg, $code);
        }
        var_dump($response, $code, $content);

        return true;
    }

    /**
    * @param string $token
    * @return Pushbullet
    */
    public function setToken($token)
    {
        $this->_token = $token;
        return $this;
    }

    /**
    * @return string
    */
    public function getToken()
    {
        return $this->_token;
    }
}