<?php

namespace App;

class Updater
{
    protected $tmp_dir;

    protected $destination;

    protected $current_version;

    protected $data;

    /**
     * Adresse de chargement du fichier JSON.
     * @var string
     */
    protected $url = "http://releases.cheky.net/releases.php";

    /**
     * Chemin du fichier cache.
     * @var string
     */
    protected $cache_filename;

    public function __construct($current_version)
    {
        $this->current_version = $current_version;
        $this->tmp_dir = DOCUMENT_ROOT."/var/tmp/".time();
        $this->cache_filename = DOCUMENT_ROOT."/var/tmp/updater.json";
        $this->destination = DOCUMENT_ROOT;
    }

    /**
    * @param string $tmp_dir
    * @return Updater
    */
    public function setTmpDir($tmp_dir)
    {
        $this->tmp_dir = $tmp_dir;
        return $this;
    }

    /**
    * @return string
    */
    public function getTmpDir()
    {
        return $this->tmp_dir;
    }

    /**
    * @param string $destination
    * @return Updater
    */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
    * @return string
    */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
    * @param string $url
    * @return Updater
    */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
    * @return string
    */
    public function getUrl()
    {
        return $this->url;
    }

    public function getTimeLastCheck()
    {
        if (!is_file($this->cache_filename)) {
            return 0;
        }

        return filectime($this->cache_filename);
    }

    public function loadData($force = false)
    {
        if (!$force && !empty($this->data)) {
            return $this->data;
        }

        if (!$force && is_file($this->cache_filename)) {
            $cache_time = $this->getTimeLastCheck();

            // Le cache est valide 1 heure
            if (($cache_time + 3600) > time()) {
                $data_str = file_get_contents($this->cache_filename);
            }
        }

        if (!isset($data_str)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!ini_get("safe_mode") && !ini_get("open_basedir")) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Cheky/".$this->current_version." PHP/".PHP_VERSION);

            curl_setopt($ch, CURLOPT_POSTFIELDS, "version=".$this->current_version);
            curl_setopt($ch, CURLOPT_URL, $this->url);
            $data_str = curl_exec($ch);

            if ($data_str === false) {
                throw new \Exception("Impossible de récupérer les informations de mise à jour : ".curl_error($ch));
            }

            // Mise en cache
            file_put_contents($this->cache_filename, $data_str);
        }

        $data = json_decode($data_str, true);

        // Vérification des données
        if (!is_array($data) || empty($data["latest"])) {
            if (is_file($this->cache_filename)) {
                unlink($this->cache_filename);
            }
            throw new \Exception("Données de mise à jour invalide.");
        }

        $this->data = $data;

        return $data;
    }

    public function getLatestVersion()
    {
        $this->loadData();

        return $this->data["latest"];
    }

    public function installFiles($version)
    {
        $this->loadData();

        if (!isset($this->data["versions"][$version])) {
            throw new \Exception("Version non trouvée.");
        }

        // Crée le répertoire temporaire si inéxistant
        if (!is_dir($this->tmp_dir)) {
            mkdir($this->tmp_dir);
        }

        // Le répertoire temporaire doit être accéssible en écriture
        if (!is_writable($this->tmp_dir)) {
            throw new \Exception("Impossible d'écrire dans '".$this->tmp_dir."'");
        }

        $version = $this->data["versions"][$version];
        $filename_zip = $this->tmp_dir."/latest.zip";

        // Téléchargement l'archive
        if (!copy($version["url"], $filename_zip)) {
            throw new \Exception("Impossible de récupérer l'archive.");
        }

        // Vérification de l'intégrité du fichier
        $hash = sha1_file($filename_zip);
        if ($hash != $version["hash"]) {
            throw new \Exception("Le fichier téléchargé semble corrompu (".$filename_zip.").\n".
                "Vous pouvez tenter de recommencer la mise à jour.\n".
                "Si le problème persiste, n'hésitez pas à venir sur le forum en parler.");
        }

        $zip = new \ZipArchive();
        if (!$zip->open($filename_zip)) {
            throw new \Exception("L'archive semble corrompue.");
        }

        // Extraction de l'archive
        $zip->extractTo($this->tmp_dir);
        $zip->close();

        unlink($filename_zip);

        // Mise à jour des fichiers locaux
        $directories = array_diff(
            scandir($this->tmp_dir),
            array(".", "..")
        );
        if (0 == count($directories)) {
            throw new \Exception("L'archive semble erronée.");
        }
        $directory = $this->tmp_dir."/".array_shift($directories);
        $this->copyFiles($directory, $this->destination);
        rmdir($directory);
        rmdir($this->tmp_dir);
    }

    public function update($fromVersion, $toVersion)
    {
        // exécute les mises à jour
        $directory = $this->destination."/others/update";
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

    protected function copyFiles($dir, $to)
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
                $this->copyFiles($dir."/".$file, $destFile);
                rmdir($dir."/".$file);
            }
        }
    }
}
