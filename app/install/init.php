<?php

if (is_file($config->getFilename()) && !isset($_GET["success"])) {
    header("LOCATION: ?mod=install&a=upgrade");
    exit;
}

if ($action == "upgrade"
    && (!isset($auth) || $auth->getUsername() != "admin")) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}