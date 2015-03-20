<?php

namespace App\Storage;

require_once DOCUMENT_ROOT."/app/models/Mail/Alert.php";

interface Alert
{
    public function fetchAll();

    public function fetchById($id);

    public function save(\App\Mail\Alert $alert);

    public function delete(\App\Mail\Alert $alert);
}