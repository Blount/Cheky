<?php

namespace App\Storage\File;

use App\Ad\Ad as AdItem;

class Ad implements \App\Storage\Ad
{
    protected $_filename;

    public function __construct($filename)
    {
        $this->_filename = $filename;
        $this->_checkFile();
    }

    public function fetchAll()
    {
        $ads = array();
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            if ($header = fgetcsv($fopen, 0, ",", '"')) {
                $nb_columns = count($header);
                while (false !== $values = fgetcsv($fopen, 0, ",", '"')) {
                    $ad = new AdItem();
                    $options = array_combine(
                        $header,
                        array_slice($values, 0, count($header))
                    );
                    if (!empty($options["photos"])) {
                        $options["photos"] = json_decode($options["photos"], true);
                    }
                    if (!empty($options["properties"])) {
                        $options["properties"] = json_decode($options["properties"], true);
                    }
                    $ad->setFromArray($options);
                    $ads[$ad->getId()] = $ad;
                }
            }
            fclose($fopen);
        }
        return $ads;
    }

    public function fetchById($id)
    {
        $ad = null;
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            if ($header = fgetcsv($fopen, 0, ",", '"')) {
                while (false !== $values = fgetcsv($fopen, 0, ",", '"')) {
                    $options = array_combine(
                        $header,
                        array_slice($values, 0, count($header))
                    );
                    if ($options["id"] == $id) {
                        $ad = new AdItem();
                        if (!empty($options["photos"])) {
                            $options["photos"] = json_decode($options["photos"], true);
                        }
                        if (!empty($options["properties"])) {
                            $options["properties"] = json_decode($options["properties"], true);
                        }
                        $ad->setFromArray($options);
                        break;
                    }
                }
            }
            fclose($fopen);
        }
        return $ad;
    }

    public function save(AdItem $ad)
    {
        $ads = $this->fetchAll();

        $fopen = fopen($this->_filename, "a");
        flock($fopen, LOCK_EX);
        $fpNewFile = fopen($this->_filename.".new", "w");
        flock($fpNewFile, LOCK_EX);

		// Entête du fichier CSV
        $headers = array_keys($ad->toArray());
        fputcsv($fpNewFile, $headers, ",", '"');

        $updated = false;
        foreach ($ads AS $a) {
            if ($a->getId() == $ad->getId()) {
                $a = $ad;
                $updated = true;
            }
            $data = $a->toArray();
            $data["photos"] = json_encode($data["photos"]);
            $data["properties"] = json_encode($data["properties"]);
            if (empty($data["date_created"])) {
				$data["date_created"] = date("Y-m-d H:i:s");
			}
            fputcsv($fpNewFile, $data, ",", '"');
        }
        if (!$updated) {
            $data = $ad->toArray();
            $data["photos"] = json_encode($data["photos"]);
            $data["properties"] = json_encode($data["properties"]);
            if (empty($data["date_created"])) {
				$data["date_created"] = date("Y-m-d H:i:s");
			}
            fputcsv($fpNewFile, $data, ",", '"');
        }

        fclose($fpNewFile);
        fclose($fopen);
        file_put_contents($this->_filename, file_get_contents($this->_filename.".new"));
        unlink($this->_filename.".new");
        return $this;
    }

    public function delete(AdItem $ad)
    {
        $ads = $this->fetchAll();
        $fopen = fopen($this->_filename, "a");
        flock($fopen, LOCK_EX);
        $fpNewFile = fopen($this->_filename.".new", "w");
        flock($fpNewFile, LOCK_EX);

		// Entête du fichier CSV
        $headers = array_keys($ad->toArray());
        fputcsv($fpNewFile, $headers, ",", '"');

        unset($ads[$ad->getId()]);
        foreach ($ads AS $a) {
            $data = $a->toArray();
            $data["photos"] = json_encode($data["photos"]);
            $data["properties"] = json_encode($data["properties"]);
            fputcsv($fpNewFile, $data, ",", '"');
        }

        fclose($fpNewFile);
        fclose($fopen);
        file_put_contents($this->_filename, file_get_contents($this->_filename.".new"));
        unlink($this->_filename.".new");

        // Si aucune annonce trouvée, on supprime le fichier CSV
        $ads = $this->fetchAll();
        if (0 == count($ads) && is_file($this->_filename)) {
			unlink($this->_filename);
        }

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
