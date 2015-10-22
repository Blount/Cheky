<?php

namespace Message;

require_once __DIR__."/Abstract.php";

class Pushover extends Message_Abstract
{
    protected $_notify_url = "https://api.pushover.net/1/messages.json";

    /**
     * @var string
     */
    protected $_token;

    /**
     * @var string
     */
    protected $_userkey;

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
        if (!$this->_token || !$this->_userkey) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        $params = array(
            "token" => $this->getToken(),
            "user" => $this->getUserkey(),
            "message" => $message,
        );
        foreach (array("title", "url", "url_title") AS $key) {
            if (isset($options[$key])) {
                $params[$key] = $options[$key];
            }
        }

        curl_setopt($this->_curl, CURLOPT_URL, $this->_notify_url);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_HEADER, false);
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $params);

        $response = curl_exec($this->_curl);

        if ($response === false) {
            throw new \Exception("cURL Error: " . curl_error($this->_curl));
        }

        $code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        if (200 !== $code) {
            $response = json_decode($response, true);
            throw new \Exception("Errors:\n" . implode("\n", $response["errors"]));
        }

        return true;
    }

    /**
    * @param string $userkey
    * @return Pushbullet
    */
    public function setUserkey($userkey)
    {
        $this->_userkey = $userkey;
        return $this;
    }

    /**
    * @return string
    */
    public function getUserkey()
    {
        return $this->_userkey;
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