<?php

namespace App\Storage\Db;

class Alert implements \App\Storage\Alert
{
    /**
     * @var \mysqli
     */
    protected $_connection;

    protected $_table = "LBC_Alert";

    /**
     * @var \App\User\User
     */
    protected $_user;

    public function __construct(\mysqli $connection, \App\User\User $user)
    {
        $this->_connection = $connection;
        $this->_user = $user;
    }

    public function fetchAll()
    {
        $alerts = array();
        $alertsDb = $this->_connection->query("SELECT * FROM ".$this->_table
            ." WHERE user_id = ".$this->_user->getId());
        while ($alertDb = $alertsDb->fetch_assoc()) {
            $alert = new \App\Mail\Alert();
            if (isset($alertDb["last_id"]) && !is_numeric($alertDb["last_id"])) {
                $alertDb["last_id"] = json_decode($alertDb["last_id"], true);
                if (!is_array($alertDb["last_id"])) {
                    $alertDb["last_id"] = array();
                }
            }
            $alert->fromArray($alertDb);
            $alert->id = $alertDb["idstr"];
            $alerts[] = $alert;
        }
        return $alerts;
    }

    public function fetchById($id)
    {
        $alert = null;
        $alertDb = $this->_connection->query(
            "SELECT * FROM ".$this->_table." WHERE user_id = ".$this->_user->getId()."
                AND idstr = '".$this->_connection->real_escape_string($id)."'")
            ->fetch_assoc();
        if ($alertDb) {
            $alert = new \App\Mail\Alert();
            if (isset($alertDb["last_id"]) && !is_numeric($alertDb["last_id"])) {
                $alertDb["last_id"] = json_decode($alertDb["last_id"], true);
                if (!is_array($alertDb["last_id"])) {
                    $alertDb["last_id"] = array();
                }
            }
            $alert->fromArray($alertDb);
            $alert->id = $alertDb["idstr"];
        }
        return $alert;
    }

    public function save(\App\Mail\Alert $alert, $forceInsert = false)
    {
        $options = $alert->toArray();

        if (is_array($options["last_id"])) {
            $options["last_id"] = json_encode($options["last_id"]);
        }

        if (!$alert->id || $forceInsert) {
            $options["user_id"] = $this->_user->getId();
            if (!$alert->id) {
                $id = sha1(uniqid());
                $alert->id = $id;
            }
            $options["idstr"] = $alert->id;
            unset($options["id"]);
            $sqlOptions = array();
            foreach ($options AS $name => $value) {
                if ($value === null) {
                    $value = "NULL";
                } elseif (is_bool($value)) {
                    $value = (int) $value;
                } elseif (!is_numeric($value)) {
                    $value = "'".$this->_connection->real_escape_string($value)."'";
                }
                $sqlOptions[$name] = $value;
            }
            $this->_connection->query("INSERT INTO ".$this->_table.
                " (`".implode("`, `", array_keys($options)).
                "`, `date_created`) VALUES (".implode(", ", $sqlOptions).", NOW())");
        } else {
            $idStr = $options["id"];
            $sqlOptions = array();
            unset($options["id"]);
            foreach ($options AS $name => $value) {
                if ($value === null) {
                    $value = "NULL";
                } elseif (is_bool($value)) {
                    $value = (int) $value;
                } elseif (!is_numeric($value)) {
                    $value = "'".$this->_connection->real_escape_string($value)."'";
                }
                $sqlOptions[] = "`".$name."` = ".$value;
            }
            $this->_connection->query("UPDATE ".$this->_table." SET
                ".implode(",", $sqlOptions).
                " WHERE idstr = '".$this->_connection->real_escape_string($idStr)."'");
        }
        return $this;
    }

    public function delete(\App\Mail\Alert $alert)
    {
        $this->_connection->query("DELETE FROM ".$this->_table."
            WHERE idstr = '".$this->_connection->real_escape_string($alert->id)."'");
        return $this;
    }

    public function fetchGroups()
    {
        $groups_db = $this->_connection->query("
            SELECT `group`
            FROM `".$this->_table."`
            WHERE `user_id` = ".$this->_user->getId()."
                AND `group` != ''
                AND `group` IS NOT NULL
            GROUP By `group`
        ");

        $groups = array();
        while ($group = $groups_db->fetch_assoc()) {
            $groups[] = $group["group"];
        }

        return $groups;
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
