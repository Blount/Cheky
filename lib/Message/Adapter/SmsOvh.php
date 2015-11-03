<?php

namespace Message\Adapter;

class SmsOvh extends AdapterAbstract
{
    protected $notify_url = "https://www.ovh.com/cgi-bin/sms/http2sms.cgi";
    protected $account;
    protected $login;
    protected $password;
    protected $from;
    protected $to;

    protected $curl_options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
    );

    /**
    * @param string $account
    * @return Ovh
    */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
    * @return string
    */
    public function getAccount()
    {
        return $this->account;
    }

    /**
    * @param string $login
    * @return Ovh
    */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
    * @return string
    */
    public function getLogin()
    {
        return $this->login;
    }

    /**
    * @param string $password
    * @return Ovh
    */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
    * @return string
    */
    public function getPassword()
    {
        return $this->password;
    }

    /**
    * @param string $from
    * @return Ovh
    */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
    * @return string
    */
    public function getFrom()
    {
        return $this->from;
    }

    /**
    * @param string $to
    * @return Ovh
    */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
    * @return string
    */
    public function getTo()
    {
        return $this->to;
    }

    public function send($message, array $options = array())
    {
        $this->setOptions($options);
        $message = trim($message);
        if ($url = $this->getUrl()) {
            $message .= ": ".$url;
        }
        $url = $this->notify_url."?".http_build_query(array(
            "account" => $this->getAccount(),
            "login" => $this->getLogin(),
            "password" => $this->getPassword(),
            "from" => $this->getFrom(),
            "to" => $this->getTo(),
            "message" => utf8_decode($message),
            "contentType" => "text/json"
        ));
        $curl = $this->getCurl(array(
            CURLOPT_URL => $url
        ));
        $content = curl_exec($curl);

        if (!$content) {
            throw new \Exception("Aucun contenu récupéré.");
        }
        $data = json_decode($content, true);
        if (null === $data) {
            throw new \Exception("Données JSON incorrecte.");
        }
        if ($data["status"] < 100 || $data["status"] > 200) {
            throw new \Exception($data["message"], $data["status"]);
        }
        return $data;
    }
}