<?php

namespace Message;

require_once __DIR__."/Abstract.php";

class Pushbullet extends \Message\Message_Abstract
{
    protected $_url = "https://api.pushbullet.com/v2/pushes";
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
     * Envoi un message par SMS.
     * @param string $msg
     * @throws \Exception
     */
    public function send($msg, $title = "")
    {
        $msg = trim($msg);
        if (!$this->_token || empty($msg)) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }

        $content = array(
            "type" => "note",
            "title" => $title,
            "body" => $msg
        );
        $content = json_encode($content);

        curl_setopt($this->_curl, CURLOPT_USERPWD, $this->getToken());
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json ",
            "Content-Length: ".strlen($content)
        ));
        curl_setopt($this->_curl, CURLOPT_URL, $this->_url);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_HEADER, false);
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $content);

        $response = curl_exec($this->_curl);

        if ($response === false) {
            throw new \Exceptions("cURL Error: " . curl_error($this->_curl));
        }

        $json = json_decode($response, true);

        if (400 <= $code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE)) {
            throw new \Exception($json["error"]["message"], $code);
        }

        return $json;
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