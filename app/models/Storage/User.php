<?php

namespace App\Storage;

require_once DOCUMENT_ROOT."/app/models/User/User.php";

interface User
{
    public function fetchAll();

    public function fetchByUsername($username);

    public function save(\App\User\User $user);

    public function delete(\App\User\User $user);
}