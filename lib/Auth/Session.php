<?php

namespace Auth;

require_once dirname(__FILE__)."/Abstract.php";

class Session extends AuthAbstract
{
    public function __construct(\App\Storage\User $storage)
    {
        session_name("lbcalerte");
        session_start();
        if (isset($_SESSION["lbcauth"])) {
            if (isset($_SESSION["lbcauth"]["username"])) {
                $this->_username = $_SESSION["lbcauth"]["username"];
            }
            if (isset($_SESSION["lbcauth"]["password"])) {
                $this->_password = $_SESSION["lbcauth"]["password"];
            }
        }
        parent::__construct($storage);
    }

    public function __destruct()
    {
        session_write_close();
    }

    public function clear()
    {
        unset($_SESSION["lbcauth"]);
    }

    public function authenticate()
    {
        if ($user = parent::authenticate()) {
            $_SESSION["lbcauth"] = array(
                "username" => $user->getUsername(),
                "password" => $user->getPassword()
            );
            return $user;
        }
        return null;
    }
}