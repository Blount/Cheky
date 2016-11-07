<?php

namespace App\Storage\Db;

use App\Ad\Ad as AdItem;

class Ad implements \App\Storage\Ad
{
    /**
     * @var \mysqli
     */
    protected $_connection;

    protected $_table = "LBC_BackupAd";

    /**
     * @var \App\User\User
     */
    protected $_user;

    public function __construct(\mysqli $connection, \App\User\User $user)
    {
        $this->_connection = $connection;
        $this->_user = $user;
    }

    public function fetchAll($order = null)
    {
        $ads = array();
        $query = "SELECT * FROM ".$this->_table
            ." WHERE user_id = ".$this->_user->getId();
        if ($order) {
            if (is_string($order)) {
                $query .= " ORDER By ".$order;
            } elseif (is_array($order)) {
                $query .= implode(", ", $order);
            }
        }
        $adsDb = $this->_connection->query($query);
        while ($adDb = $adsDb->fetch_assoc()) {
            foreach (array("photos", "properties") AS $key) {
                $adDb[$key] = json_decode($adDb[$key], true);
                if (!$adDb[$key]) {
                    $adDb[$key] = array();
                }
            }
            $ad = new AdItem();
            $ad->setFromArray($adDb);
            $ads[] = $ad;
        }
        return $ads;
    }

    public function fetchById($id)
    {
        $ad = null;
        $adDb = $this->_connection->query(
            "SELECT * FROM ".$this->_table." WHERE user_id = ".$this->_user->getId()."
                AND id = '".$this->_connection->real_escape_string($id)."'")
            ->fetch_assoc();
        if ($adDb) {
            foreach (array("photos", "properties") AS $key) {
                $adDb[$key] = json_decode($adDb[$key], true);
                if (!$adDb[$key]) {
                    $adDb[$key] = array();
                }
            }
            $ad = new AdItem();
            $ad->setFromArray($adDb);
        }
        return $ad;
    }

    public function save(AdItem $ad)
    {
        $options = $ad->toArray();
        $options["photos"] = json_encode($options["photos"]);
        $options["properties"] = json_encode($options["properties"]);

        if (!$ad->getAid()) {
            $options["user_id"] = $this->_user->getId();
            unset($options["aid"]);
            if (empty($options["date_created"])) {
                $options["date_created"] = date("Y-m-d H:i:s");
            }
            $sqlOptions = array();
            foreach ($options AS $name => $value) {
                if ($value === null) {
                    $value = "NULL";
                } elseif (is_bool($value)) {
                    $value = (int) $value;
                } else {
                    $value = "'".$this->_connection->real_escape_string($value)."'";
                }
                $sqlOptions[$name] = $value;
            }
            $this->_connection->query("INSERT INTO ".$this->_table.
                " (`".implode("`, `", array_keys($options)).
                "`) VALUES (".implode(", ", $sqlOptions).")");
            if ($this->_connection->error) {
                var_dump($this->_connection->error);
                exit;
            }
        } else {
            $sqlOptions = array();
            unset($options["aid"]);
            foreach ($options AS $name => $value) {
                if ($value === null) {
                    $value = "NULL";
                } elseif (is_bool($value)) {
                    $value = (int) $value;
                } else {
                    $value = "'".$this->_connection->real_escape_string($value)."'";
                }
                $sqlOptions[] = "`".$name."` = ".$value;
            }
            $this->_connection->query("UPDATE ".$this->_table." SET
                ".implode(",", $sqlOptions).
                " WHERE `aid` = ".$ad->getAid());
            if ($this->_connection->error) {
                var_dump($this->_connection->error);
                exit;
            }
        }

        return $this;
    }

    public function delete(AdItem $ad)
    {
        $this->_connection->query("DELETE FROM ".$this->_table."
            WHERE `aid` = ".$ad->getAid());

        return $this;
    }

    /**
     * @param \mysqli $dbConnection
     * @return \App\Storage\Db\Ad
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
