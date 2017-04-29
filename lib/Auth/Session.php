<?php

namespace Auth;

require_once __DIR__."/Abstract.php";

class Session extends AuthAbstract
{
    public function __construct(\App\Storage\User $storage)
    {
        session_name("cheky");
        session_start();
        if (isset($_SESSION["chekyauth"])) {
            if (isset($_SESSION["chekyauth"]["username"])) {
                $this->_username = $_SESSION["chekyauth"]["username"];
            }
            if (isset($_SESSION["chekyauth"]["password"])) {
                $this->_password = $_SESSION["chekyauth"]["password"];
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
        unset($_SESSION["chekyauth"]);
    }

    public function authenticate()
    {
        if ($user = parent::authenticate()) {
            $_SESSION["chekyauth"] = array(
                "username" => $user->getUsername(),
                "password" => $user->getPassword()
            );
            return $user;
        }
        return null;
    }
}