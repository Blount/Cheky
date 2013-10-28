<?php

if (!isset($_GET["username"]) || !$user = $userStorage->fetchByUsername($_GET["username"])) {
    header("LOCATION: ?mod=admin&a=users");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userStorage->delete($user);
    $configAlert = DOCUMENT_ROOT."/var/configs/".$user->getUsername().".csv";
    if (is_file($configAlert)) {
        unlink($configAlert);
    }
    header("LOCATION: ?mod=admin&a=users");
    exit;
}