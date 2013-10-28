<?php

namespace App\User;

class User
{
    protected $_username;
    protected $_password;

    public function __construct(array $options = array())
    {
        if (isset($options["username"])) {
            $this->setUsername($options["username"]);
        }
        if (isset($options["password"])) {
            $this->setPassword($options["password"]);
        }
    }

    /**
    * @param string $username
    * @return User
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
    * @return User
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
     * Indique si l'utilisateur est administrateur.
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->getUsername() == "admin";
    }
}