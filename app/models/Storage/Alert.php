<?php

namespace App\Storage;

interface Alert
{
    public function fetchAll();

    public function fetchById($id);

    public function save(\App\Mail\Alert $alert);

    public function delete(\App\Mail\Alert $alert);
}