<?php

namespace App\User;

class User
{
    protected $_username;
    protected $_password;
    protected $_options = array(
        "free_mobile_user" => "",
        "free_mobile_key" => ""
    );
    protected $_optionsLoaded = false;

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

    public function getOption($name)
    {
        return isset($this->_options[$name])?$this->_options[$name]:null;
    }

    public function setOption($name, $value)
    {
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }
        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function setOptions(array $options)
    {
        $this->_options = array_merge($this->_options,
                array_intersect_key($options, $this->_options));
        return $this;
    }
}