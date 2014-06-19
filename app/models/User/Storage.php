<?php

namespace App\User;

require_once DOCUMENT_ROOT."/app/models/User/User.php";

class Storage
{
    protected $_filename;

    public function __construct($filename)
    {
        $this->_filename = $filename;
        $this->_checkFile();
    }

    public function fetchAll()
    {
        $users = array();
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            while (false !== $value = fgets($fopen)) {
                $value = trim($value);
                $user = new User();
                $user->setPassword(substr($value, 0, 40))
                    ->setUsername(substr($value, 40));
                $this->_loadUserOptions($user);
                $users[] = $user;
            }
            fclose($fopen);
        }
        return $users;
    }

    public function fetchByUsername($username)
    {
        $user = null;
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            while (false !== $value = fgets($fopen)) {
                $value = trim($value);
                if (substr($value, 40) == $username) {
                    $user = new User();
                    $user->setPassword(substr($value, 0, 40))
                        ->setUsername($username);
                    $this->_loadUserOptions($user);
                    break;
                }
            }
            fclose($fopen);
        }
        return $user;
    }

    public function save(User $user)
    {
        if (!$this->fetchByUsername($user->getUsername()) || !is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "a");
            fputs($fopen, $user->getPassword().$user->getUsername()."\n");
            fclose($fopen);
        } else {
            $fopen = fopen($this->_filename, "r");
            $fpNewFile = fopen($this->_filename.".new", "w");
            flock($fopen, LOCK_EX);
            while (false !== $value = fgets($fopen)) {
                $value = trim($value);
                if (substr($value, 40) == $user->getUsername()) {
                    $value = $user->getPassword().$user->getUsername();
                }
                fputs($fpNewFile, $value."\n");
            }
            fclose($fopen);
            fclose($fpNewFile);
            file_put_contents($this->_filename, file_get_contents($this->_filename.".new"));
            unlink($this->_filename.".new");
            $this->_saveUserOptions($user);
        }
        return $this;
    }

    public function delete(User $user)
    {
        if (is_file($this->_filename)) {
            $fopen = fopen($this->_filename, "r");
            $fpNewFile = fopen($this->_filename.".new", "w");
            while (false !== $value = fgets($fopen)) {
                $value = trim($value);
                if (substr($value, 40) != $user->getUsername()) {
                    fputs($fpNewFile, $value."\n");
                }
            }
            fclose($fopen);
            fclose($fpNewFile);
            file_put_contents($this->_filename, file_get_contents($this->_filename.".new"));
            unlink($this->_filename.".new");
            $this->_deleteUserOptions($user);
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

    protected function _loadUserOptions(User $user)
    {
        $dir = DOCUMENT_ROOT.DS."var".DS."configs";
        $filename = $dir.DS."user_".$user->getUsername().".json";
        if (is_file($filename)) {
            $data = json_decode(trim(file_get_contents($filename)), true);
            if ($data && is_array($data)) {
                $user->setOptions($data);
            }
        }
        return $this;
    }

    protected function _saveUserOptions(User $user)
    {
        $dir = DOCUMENT_ROOT.DS."var".DS."configs";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $filename = $dir.DS."user_".$user->getUsername().".json";
        file_put_contents($filename, json_encode($user->getOptions()));
        return $this;
    }

    protected function _deleteUserOptions($user)
    {
        $dir = DOCUMENT_ROOT.DS."var".DS."configs";
        $filename = $dir.DS."user_".$user->getUsername().".json";
        if (is_file($filename)) {
            unlink($filename);
        }
        return $this;
    }
}