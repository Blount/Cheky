<?php

namespace App\Storage;

use App\Ad\Ad as AdItem;

interface Ad
{
    public function fetchAll();

    public function fetchById($id);

    public function save(AdItem $ad);

    public function delete(AdItem $ad);

    public function fetchTags();
}