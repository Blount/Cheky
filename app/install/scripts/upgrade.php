<?php

if (!is_file($config->getFilename())) {
    header("LOCATION: ?mod=install");
    exit;
}

if ($auth->getUsername() != "admin") {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$require_upgrade = false;

if (-1 == version_compare($config->get("general", "version"), APPLICATION_VERSION)) {
    $require_upgrade = true;
}