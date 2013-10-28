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
$version = require DOCUMENT_ROOT."/version.php";

if (-1 == version_compare($config->get("general", "version"), $version)) {
    $require_upgrade = true;
}