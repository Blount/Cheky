<?php

if ($auth->getUsername() != "admin") {
    header("HTTP/1.1 403 Forbidden");
    exit;
}