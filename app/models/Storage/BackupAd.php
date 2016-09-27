<?php

namespace App\Storage;

use App\BackupAd\Ad;

interface BackupAd
{
    public function fetchAll();

    public function fetchById($id);

    public function save(Ad $ad);

    public function delete(Ad $ad);
}