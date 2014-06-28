<?php

namespace SMS;

class FreeMobile
{
    protected $_url = "https://smsapi.free-mobile.fr/sendmsg";
    protected $_user;
    protected $_key;

    protected $_curl;

    public function __construct()
    {
        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
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
    public function send($msg)
    {
        $msg = trim($msg);
        if (!$this->_user || !$this->_key || empty($msg)) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }
        curl_setopt($this->_curl, CURLOPT_URL, $this->_url."?user=".$this->_user.
            "&pass=".$this->_key.
            "&msg=".urlencode($msg));
        curl_exec($this->_curl);
        if (200 != $code = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE)) {
            switch ($code) {
                case 400: $message = "Un des paramètres obligatoires est manquant."; break;
                case 402: $message = "Trop de SMS ont été envoyés en trop peu de temps."; break;
                case 403: $message = "Vous n'avez pas activé la notification SMS dans votre espace abonné Free Mobile ou votre identifiant/clé est incorrect."; break;
                case 500: $message = "erreur sur serveur Free Mobile."; break;
                default: $message = "erreur inconnue.";
            }
            throw new \Exception($message, $code);
        }
        return $this;
    }

    /**
    * @param string $user
    * @return \SMS\FreeMobile
    */
    public function setUser($user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
    * @return string
    */
    public function getUser()
    {
        return $this->_user;
    }

    /**
    * @param string $key
    * @return \SMS\FreeMobile
    */
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    /**
    * @return string
    */
    public function getKey()
    {
        return $this->_key;
    }
}