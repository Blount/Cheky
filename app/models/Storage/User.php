<?php

namespace App\Storage;

interface User
{
    public function fetchAll();

    public function fetchByUsername($username);

    public function save(\App\User\User $user);

    public function delete(\App\User\User $user);
}