<?php

namespace Auth;

require_once dirname(__FILE__)."/Abstract.php";

class Basic extends AuthAbstract
{
    public function __construct(\App\Storage\User $storage)
    {
        if (!$this->_username && isset($_SERVER["PHP_AUTH_USER"])) {
            $this->setUsername($_SERVER["PHP_AUTH_USER"]);
        }
        if (!$this->_password && isset($_SERVER["PHP_AUTH_PW"])) {
            $this->setPassword(sha1($_SERVER["PHP_AUTH_PW"]));
        }
        parent::__construct($storage);
    }
}