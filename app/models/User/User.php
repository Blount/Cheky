<?php

namespace App\User;

class User
{
    protected $_id;
    protected $_username;
    protected $_password;
    protected $_options = array(
        "free_mobile_user" => "",
        "free_mobile_key" => "",
        "unique_ads" => false
    );
    protected $_optionsLoaded = false;

    public function __construct(array $options = array())
    {
        if (isset($options["id"])) {
            $this->setUsername($options["id"]);
        }
        if (isset($options["username"])) {
            $this->setUsername($options["username"]);
        }
        if (isset($options["password"])) {
            $this->setPassword($options["password"]);
        }
    }

    /**
    * @param int $id
    * @return User
    */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
    * @return int
    */
    public function getId()
    {
        return $this->_id;
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
        $this->_options["unique_ads"] = (bool) $this->_options["unique_ads"];
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
        $this->_options["unique_ads"] = (bool) $this->_options["unique_ads"];
        return $this;
    }
}