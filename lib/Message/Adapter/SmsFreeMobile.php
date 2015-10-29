<?php

namespace Message\Adapter;

class SmsFreeMobile extends AdapterAbstract
{
    protected $notify_url = "https://smsapi.free-mobile.fr/sendmsg";
    protected $user;
    protected $key;

    protected $curl_options = array(
        CURLOPT_SSL_VERIFYPEER => false
    );

    /**
     * @param string $message
     * @throws \Exception
     */
    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        if (!$this->user || !$this->key) {
            throw new \Exception("Un des paramètres obligatoires est manquant", 400);
        }
        $message = trim($message);
        if ($url = $this->getUrl()) {
            $message .= ": ".$url;
        }
        $curl = $this->getCurl(array(
            CURLOPT_URL => $this->notify_url."?user=".$this->user.
                "&pass=".$this->key.
                "&msg=".urlencode($message)
        ));
        curl_exec($curl);
        if (200 != $code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
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
    * @return \Message\SMS\FreeMobile
    */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
    * @return string
    */
    public function getUser()
    {
        return $this->user;
    }

    /**
    * @param string $key
    * @return \Message\SMS\FreeMobile
    */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
    * @return string
    */
    public function getKey()
    {
        return $this->key;
    }
}