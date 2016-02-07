<?php

namespace App\User;

class User
{
    protected $_id;
    protected $_username;
    protected $_password;
    protected $_options = array();
    protected $_optionsLoaded = false;

    public function __construct(array $options = array())
    {
        if (isset($options["id"])) {
            $this->setId($options["id"]);
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
        if (!$this->_id) {
            return md5($this->_username);
        }
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
     * Retourne vrai si la notification SMS Free Mobile est activée.
     * @return boolean
     */
    public function hasSMSFreeMobile()
    {
        return false != $this->getOption("notification.freeMobile");
    }

    /**
     * Retourne vrai si la notification SMS OVH est activée.
     * @return boolean
     */
    public function hasSMSOvh()
    {
        return false != $this->getOption("notification.ovh");
    }

    /**
     * Retourne vrai si la notification Pushbullet est activée.
     * @return boolean
     */
    public function hasPushbullet()
    {
        return false != $this->getOption("notification.pushbullet");
    }

    /**
     * Retourne vrai si la notification NotifyMyAndroid est activée.
     * @return boolean
     */
    public function hasNotifyMyAndroid()
    {
        return false != $this->getOption("notification.notifymyandroid");
    }

    /**
     * Retourne vrai si la notification Pushover est activée.
     * @return boolean
     */
    public function hasPushover()
    {
        return false != $this->getOption("notification.pushover");
    }

    /**
     * Indique si l'utilisateur est administrateur.
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->getUsername() == "admin";
    }

    public function getOption($name, $default = null)
    {
        if (strpos($name, ".")) {
            $options = explode(".", $name);
            $nbOptions = count($options);
            $current = $this->_options;
            for ($i = 0; $i < $nbOptions; $i++) {
                if (is_array($current) && isset($current[$options[$i]])) {
                    $current = $current[$options[$i]];
                } else {
                    break;
                }
            }
            if ($i == $nbOptions) {
                return $current;
            }
            return $default;
        }
        return isset($this->_options[$name])?$this->_options[$name]:$default;
    }

    public function setOption($name, $value)
    {
        if (strpos($name, ".")) {
            $options = explode(".", $name);
            $nbOptions = count($options);
            $current = $value;
            for ($i = $nbOptions - 1; $i >= 0; $i--) {
                $current = array($options[$i] => $current);
            }
            $this->_options = array_replace_recursive($this->_options, $current);
        } else {
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
        $this->_options = $options;
        return $this;
    }

    public function mergeOptions(array $options)
    {
        $this->_options = array_replace_recursive($this->_options, $options);
        return $this;
    }
}