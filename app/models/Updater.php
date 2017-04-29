<?php

namespace App;

class Updater
{
    protected $_tmp_dir;

    protected $_destination;

    /**
     * Adresse pour la récupération de la dernière version
     * @var string
     */
    protected $_url_version = "https://raw.githubusercontent.com/Blount/LBCAlerte/master/version.php";

    /**
     * Adresse pour récupérer l'archive ZIP.
     * %VERSION% est remplacer parle numéro de version à télécharger.
     * @var string
     */
    protected $_url_archive = "https://github.com/Blount/LBCAlerte/archive/%VERSION%.zip";

    public function __construct()
    {
        $this->_tmp_dir = DOCUMENT_ROOT."/var/tmp/".time();
        $this->_destination = DOCUMENT_ROOT;
    }

    /**
    * @param string $tmp_dir
    * @return Updater
    */
    public function setTmpDir($tmp_dir)
    {
        $this->_tmp_dir = $tmp_dir;
        return $this;
    }

    /**
    * @return string
    */
    public function getTmpDir()
    {
        return $this->_tmp_dir;
    }

    /**
    * @param string $destination
    * @return Updater
    */
    public function setDestination($destination)
    {
        $this->_destination = $destination;
        return $this;
    }

    /**
    * @return string
    */
    public function getDestination()
    {
        return $this->_destination;
    }

    /**
    * @param string $url_version
    * @return Updater
    */
    public function setUrlVersion($url_version)
    {
        $this->_url_version = $url_version;
        return $this;
    }

    /**
    * @return string
    */
    public function getUrlVersion()
    {
        return $this->_url_version;
    }

    /**
    * @param string $url_archive
    * @return Updater
    */
    public function setUrlArchive($url_archive)
    {
        $this->_url_archive = $url_archive;
        return $this;
    }

    /**
    * @return string
    */
    public function getUrlArchive()
    {
        return $this->_url_archive;
    }

    public function getLastVersion()
    {
        $lastVersion = file_get_contents($this->getUrlVersion());
        if (preg_match('#return\s+"(.*)"\s*;#imsU', $lastVersion, $m)) {
            return $m[1];
        }
        throw new \Exception("Impossible de récupérer la dernière version.");
    }

    public function installFiles($version)
    {
        $tmpZip = $this->_tmp_dir."/latest.zip";
        if (!is_dir($this->_tmp_dir)) {
            mkdir($this->_tmp_dir);
        }
        if (!is_writable($this->_tmp_dir)) {
            throw new \Exception("Impossible d'écrire dans '".$this->_tmp_dir."'");
        }
        $archive = str_replace("%VERSION%", $version, $this->_url_archive);
        if (!is_file($tmpZip) && !copy($archive, $tmpZip)) {
            throw new \Exception("Impossible de récupérer l'archive.");
        }
        $zip = new \ZipArchive();
        if (!$zip->open($tmpZip)) {
            throw new \Exception("L'archive semble erronée.");
        }
        // extraire l'archive
        $zip->extractTo($this->_tmp_dir);
        $zip->close();

        // mise à jour des fichiers.
        $this->_copyFiles($this->_tmp_dir."/Cheky-".$version, $this->_destination);
        rmdir($this->_tmp_dir."/Cheky-".$version);
        unlink($tmpZip);
    }

    public function update($fromVersion, $toVersion)
    {
        // exécute les mises à jour
        $directory = $this->_destination."/others/update";
        if (is_dir($directory)) {
            $filenames = scandir($directory);
            $filenames_php = array();
            foreach ($filenames AS $filename) {
                if ($filename != "update.php" && false !== strpos($filename, ".php")) {
                    $filenames_php[basename($filename, ".php")] = $filename;
                }
            }
            $versions = array_keys($filenames_php);
            usort($versions, function ($a1, $a2) {
                return version_compare($a1, $a2, "<") ? -1 : 1;
            });
            foreach ($versions AS $version) {
                if (version_compare($fromVersion, $version, "<")
                    && version_compare($toVersion, $version, ">=")) {
                    require $directory."/".$filenames_php[$version];
                    $class = "Update_".str_replace(".", "", $version);
                    if (class_exists($class, false)) {
                        $class = new $class();
                        $class->update();
                    }
                }
            }
        }
    }

    protected function _copyFiles($dir, $to)
    {
        foreach (scandir($dir) AS $file) {
            if ($file == "." || $file == "..") {
                continue;
            }
            $destFile = $to."/".$file;
            if (is_file($dir."/".$file)) {
                rename($dir."/".$file, $destFile);
            } elseif (is_dir($dir."/".$file)) {
                if (!is_dir($destFile)) {
                    mkdir($destFile);
                }
                $this->_copyFiles($dir."/".$file, $destFile);
                rmdir($dir."/".$file);
            }
        }
    }
}
