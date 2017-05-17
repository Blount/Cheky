<?php

namespace Auth;

require_once __DIR__."/Abstract.php";

class RssKey extends AuthAbstract
{
    public function __construct(\App\Storage\User $storage)
    {
        if (!$this->_username && isset($_GET["u"])) {
            $this->setUsername($_GET["u"]);
        }
        if (!$this->_password && isset($_GET["key"])) {
            $this->setPassword($_GET["key"]);
        }
        parent::__construct($storage);
    }

    public function authenticate()
    {
        if (!$this->_username || !$this->_password) {
            return null;
        }
        $user = $this->_storage->fetchByUsername($this->_username);
        if ($user && $user->getRssKey() == $this->_password) {
            return $user;
        }
        return null;
    }
}