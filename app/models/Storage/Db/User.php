<?php

namespace App\Storage\Db;

require_once DOCUMENT_ROOT."/app/models/User/User.php";
require_once __DIR__."/../User.php";

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
                ->setUsername($userDb->username)
                ->setOptions(array(
                    "free_mobile_user" => $userDb->free_mobile_user,
                    "free_mobile_key" => $userDb->free_mobile_key
                ));
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
                ->setUsername($userDb->username)
                ->setOptions(array(
                    "free_mobile_user" => $userDb->free_mobile_user,
                    "free_mobile_key" => $userDb->free_mobile_key
                ));
        }
        return $user;
    }

    public function save(\App\User\User $user)
    {
        if (!$this->fetchByUsername($user->getUsername())) {
            $this->_connection->query("INSERT INTO ".$this->_table.
                " (username, password, free_mobile_user, free_mobile_key) VALUES (
                    '".$this->_connection->real_escape_string($user->getUsername())."',
                    '".$this->_connection->real_escape_string($user->getPassword())."',
                    '".$this->_connection->real_escape_string($user->getOption("free_mobile_user"))."',
                    '".$this->_connection->real_escape_string($user->getOption("free_mobile_key"))."'
                )");
        } else {
            $this->_connection->query("UPDATE ".$this->_table." SET
                password = '".$this->_connection->real_escape_string($user->getPassword())."',
                free_mobile_user = '".$this->_connection->real_escape_string($user->getOption("free_mobile_user"))."',
                free_mobile_key = '".$this->_connection->real_escape_string($user->getOption("free_mobile_key"))."'
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