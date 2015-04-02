<?php

abstract class Update
{
    /**
     * @var Config_Lite
     */
    protected $_config;

    /**
     * @var string
     */
    protected $_storage;

    /**
     * @var mysqli
     */
    protected $_dbConnection;

    /**
     * @var \App\Storage\User
     */
    protected $_userStorage;

    /**
     * gloal, c'est mal, mais ça conviendra pour l'instant.
     */
    public function __construct()
    {
        global $config, $userStorage;
        $this->_config = $config;
        $this->_storage = $config->get("storage", "type", "files");
        $this->_userStorage = $userStorage;

        if ("db" == $this->_storage) {
            global $dbConnection;
            $this->_dbConnection = $dbConnection;
        }
    }

    abstract public function update();
}