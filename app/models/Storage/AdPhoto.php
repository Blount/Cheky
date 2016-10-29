<?php

namespace App\Storage;

use App\BackupAd\Ad;

class AdPhoto
{
    protected $_user;

    public function __construct(\App\User\User $user)
    {
        $this->_user = $user;
    }

    public function getPublicDestination($filename = null)
    {
        $destination = "static/media/annonce/".$this->_user->getUsername();
        if ($filename) {
            $destination .= "/".$filename;
        }
        return $destination;
    }

    public function getDestination()
    {
        return DOCUMENT_ROOT."/static/media/annonce/".$this->_user->getUsername();
    }

    public function import(Ad $ad, $override = false)
    {
        $destination = $this->getDestination();
        if (!is_dir($destination) && !mkdir($destination)) {
            return false;
        }

        foreach ($ad->getPhotos() AS $photo) {
            $filename = $destination."/".$photo["local"];
            if (!is_file($filename) || $override) {
                copy($photo["remote"], $filename);
            }
        }

        return true;
    }

    public function delete(Ad $ad)
    {
        $destination = $this->getDestination();
        foreach ($ad->getPhotos() AS $photo) {
            $filename = $destination."/".$photo["local"];
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }
}