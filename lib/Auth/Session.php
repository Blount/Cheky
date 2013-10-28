<?php

namespace Auth;

require_once dirname(__FILE__)."/Abstract.php";

class Session extends AuthAbstract
{
    public function __construct(\App\User\Storage $storage)
    {
        session_start();
        if (isset($_SESSION["auth"])) {
            if (isset($_SESSION["auth"]["username"])) {
                $this->_username = $_SESSION["auth"]["username"];
            }
            if (isset($_SESSION["auth"]["password"])) {
                $this->_password = $_SESSION["auth"]["password"];
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
        unset($_SESSION["auth"]);
    }

    public function authenticate()
    {
        if ($user = parent::authenticate()) {
            $_SESSION["auth"] = array(
                "username" => $user->getUsername(),
                "password" => $user->getPassword()
            );
            return $user;
        }
        return null;
    }
}