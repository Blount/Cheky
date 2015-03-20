<?php

namespace Auth;

abstract class AuthAbstract
{
    protected $_username;
    protected $_password;

    /**
     *
     * @var App\Storage\User
     */
    protected $_storage;

    public function __construct(\App\Storage\User $storage)
    {
        $this->_storage = $storage;
    }

    /**
    * @param string $username
    * @return Basic
    */
    public function setUsername($username)
    {
        $this->_username = $username;
        return $this;
    }

    /**
    * @return string
    */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
    * @param string $password
    * @return Basic
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

    public function authenticate()
    {
        if (!$this->_username || !$this->_password) {
            return null;
        }
        $user = $this->_storage->fetchByUsername($this->_username);
        if ($user && $user->getPassword() == $this->_password) {
            return $user;
        }
        return null;
    }

    public function clear() {}
}