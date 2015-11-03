<?php

namespace App\Storage\Db;

class User implements \App\Storage\User
{
    /**
     * @var \mysqli
     */
    protected $_connection;

    protected $_table = "LBC_User";

    public function __construct(\mysqli $connection)
    {
        $this->_connection = $connection;
    }

    public function fetchAll()
    {
        $users = array();
        $usersDb = $this->_connection->query("SELECT * FROM ".$this->_table);
        while ($userDb = $usersDb->fetch_object()) {
            $user = new \App\User\User();
            $user->setId($userDb->id)
                ->setPassword($userDb->password)
                ->setUsername($userDb->username);
            if (!empty($userDb->options)) {
                $options = json_decode($userDb->options, true);
                if (is_array($options)) {
                    $user->setOptions($options);
                }
            }
            $users[] = $user;
        }
        return $users;
    }

    public function fetchByUsername($username)
    {
        $user = null;
        $userDb = $this->_connection->query(
            "SELECT * FROM ".$this->_table." WHERE username = '".
            $this->_connection->real_escape_string($username)."'")
            ->fetch_object();
        if ($userDb) {
            $user = new \App\User\User();
            $user->setId($userDb->id)
                ->setPassword($userDb->password)
                ->setUsername($userDb->username);
            if (!empty($userDb->options)) {
                $options = json_decode($userDb->options, true);
                if (is_array($options)) {
                    $user->setOptions($options);
                }
            }
        }
        return $user;
    }

    public function save(\App\User\User $user)
    {
        if (!$this->fetchByUsername($user->getUsername())) {
            $this->_connection->query("INSERT INTO `".$this->_table.
                "` (`username`, `password`, `options`) VALUES (
                    '".$this->_connection->real_escape_string($user->getUsername())."',
                    '".$this->_connection->real_escape_string($user->getPassword())."',
                    '".$this->_connection->real_escape_string(json_encode($user->getOptions()))."'
                )");
        } else {
            $this->_connection->query("UPDATE `".$this->_table."` SET
                `password` = '".$this->_connection->real_escape_string($user->getPassword())."',
                `options` = '".$this->_connection->real_escape_string(json_encode($user->getOptions()))."'
            WHERE id = ".$user->getId());
        }
        return $this;
    }

    public function delete(\App\User\User $user)
    {
        $this->_connection->query("DELETE FROM ".$this->_table." WHERE id = ".$user->getId());
        return $this;
    }

    /**
     * @param \mysqli $dbConnection
     * @return \App\Storage\Db\User
     */
    public function setDbConnection($dbConnection)
    {
        $this->_connection = $dbConnection;
        return $this;
    }

    /**
     * @return \mysqli
     */
    public function getDbConnection()
    {
        return $this->_connection;
    }
}