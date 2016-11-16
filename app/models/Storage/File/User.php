<?php

namespace App\Storage\File;

class User implements \App\Storage\User
{
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
                $user = new \App\User\User();
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
                    $user = new \App\User\User();
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

    public function save(\App\User\User $user)
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

    public function delete(\App\User\User $user)
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

    protected function _loadUserOptions(\App\User\User $user)
    {
        $dir = DOCUMENT_ROOT.DS."var".DS."configs";
        $filename = $dir.DS."user_".$user->getUsername().".json";
        if (is_file($filename)) {
            $data = json_decode(trim(file_get_contents($filename)), true);
            if (isset($data["api_key"])) {
                $user->setApiKey($data["api_key"]);
                unset($data["api_key"]);
            }
            if ($data && is_array($data)) {
                if (!empty($data["notification"]) && is_array($data["notification"])) {
                    foreach ($data["notification"] AS $key => $params) {
                        if ($params && !isset($params["active"])) {
                            $data["notification"][$key]["active"] = true;
                        }
                    }
                }
                $user->setOptions($data);
            }
        }
        return $this;
    }

    protected function _saveUserOptions(\App\User\User $user)
    {
        $dir = DOCUMENT_ROOT.DS."var".DS."configs";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $filename = $dir.DS."user_".$user->getUsername().".json";
        $data = $user->getOptions();
        if ($api_key = $user->getApiKey()) {
            $data["api_key"] = $api_key;
        }
        file_put_contents($filename, json_encode($data));
        return $this;
    }

    protected function _deleteUserOptions(\App\User\User $user)
    {
        $dir = DOCUMENT_ROOT.DS."var".DS."configs";
        $filename = $dir.DS."user_".$user->getUsername().".json";
        if (is_file($filename)) {
            unlink($filename);
        }
        return $this;
    }
}