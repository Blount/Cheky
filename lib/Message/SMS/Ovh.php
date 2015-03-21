<?php

namespace Message\SMS;

require_once __DIR__."/../Abstract.php";

class Ovh extends \Message\Message_Abstract
{
    protected $_url = "https://www.ovh.com/cgi-bin/sms/http2sms.cgi";
    protected $_account;
    protected $_login;
    protected $_password;
    protected $_from;
    protected $_to;

    /**
    * @param string $account
    * @return Ovh
    */
    public function setAccount($account)
    {
        $this->_account = $account;
        return $this;
    }

    /**
    * @return string
    */
    public function getAccount()
    {
        return $this->_account;
    }

    /**
    * @param string $login
    * @return Ovh
    */
    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }

    /**
    * @return string
    */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
    * @param string $password
    * @return Ovh
    */
    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    /**
    * @return string
    */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
    * @param string $from
    * @return Ovh
    */
    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }

    /**
    * @return string
    */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
    * @param string $to
    * @return Ovh
    */
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    /**
    * @return string
    */
    public function getTo()
    {
        return $this->_to;
    }

    public function send($message)
    {
        $url = $this->_url."?".http_build_query(array(
            "account" => $this->getAccount(),
            "login" => $this->getLogin(),
            "password" => $this->getPassword(),
            "from" => $this->getFrom(),
            "to" => $this->getTo(),
            "message" => utf8_decode($message),
            "contentType" => "text/json"
        ));

        $content = file_get_contents($url);
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