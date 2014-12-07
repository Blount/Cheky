<?php

namespace App\Storage\Db;

require_once DOCUMENT_ROOT."/app/models/Mail/Alert.php";
require_once __DIR__."/../Alert.php";

class Alert implements \App\Storage\Alert
{
    protected $_filename;

    protected $_header = array(
        "email", "id", "title", "url", "interval", "time_last_ad",
        "time_updated", "price_min", "price_max", "price_strict",
        "cities", "suspend", "group", "group_ads", "categories",
        "send_mail", "send_sms"
    );

    public function __construct($filename)
    {
        $this->_filename = $filename;
        $this->_checkFile();
    }

    public function fetchAll()
    {
        $alerts = array();
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            if ($header = fgetcsv($fopen, 0, ",", '"')) {
                while (false !== $values = fgetcsv($fopen, 0, ",", '"')) {
                    $alert = new \App\Mail\Alert();
                    $alert->fromArray(array_combine($header, $values));
                    $alerts[$alert->id] = $alert;
                }
            }
            fclose($fopen);
        }
        return $alerts;
    }

    public function fetchById($id)
    {
        $alert = null;
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            if ($header = fgetcsv($fopen, 0, ",", '"')) {
                while (false !== $values = fgetcsv($fopen, 0, ",", '"')) {
                    $options = array_combine($header, $values);
                    if ($options["id"] == $id) {
                        $alert = new \App\Mail\Alert();
                        $alert->fromArray($options);
                        break;
                    }
                }
            }
            fclose($fopen);
        }
        return $alert;
    }

    public function save(\App\Mail\Alert $alert)
    {
        $alerts = $this->fetchAll();
        $fopen = fopen($this->_filename, "a");
        flock($fopen, LOCK_EX);
        $fpNewFile = fopen($this->_filename.".new", "w");
        flock($fpNewFile, LOCK_EX);

        fputcsv($fpNewFile, $this->_header, ",", '"');
        $updated = false;
        foreach ($alerts AS $a) {
            if ($a->id == $alert->id) {
                $a = $alert;
                $updated = true;
            }
            fputcsv($fpNewFile, $a->toArray(), ",", '"');
        }
        if (!$updated && !$alert->id) {
            $alert->id = sha1(uniqid());
            fputcsv($fpNewFile, $alert->toArray(), ",", '"');
        }

        fclose($fpNewFile);
        fclose($fopen);
        file_put_contents($this->_filename, file_get_contents($this->_filename.".new"));
        unlink($this->_filename.".new");
        return $this;
    }

    public function delete(\App\Mail\Alert $alert)
    {
        $alerts = $this->fetchAll();
        $fopen = fopen($this->_filename, "a");
        flock($fopen, LOCK_EX);
        $fpNewFile = fopen($this->_filename.".new", "w");
        flock($fpNewFile, LOCK_EX);

        fputcsv($fpNewFile, $this->_header, ",", '"');

        unset($alerts[$alert->id]);
        foreach ($alerts AS $a) {
            fputcsv($fpNewFile, $a->toArray(), ",", '"');
        }

        fclose($fpNewFile);
        fclose($fopen);
        file_put_contents($this->_filename, file_get_contents($this->_filename.".new"));
        unlink($this->_filename.".new");
        return $this;
    }

    protected function _checkFile()
    {
        if (empty($this->_filename)) {
            throw new \Exception("Un fichier doit être spécifié.");
        }
        $dir = dirname($this->_filename);
        if (!is_file($this->_filename)) {
            if (!is_writable($dir)) {
                throw new \Exception("Pas d'accès en écriture sur le répertoire '".$dir."'.");
            }
        } elseif (!is_writable($this->_filename)) {
            throw new \Exception("Pas d'accès en écriture sur le fichier '".$this->_filename."'.");
        }
    }
}