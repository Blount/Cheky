<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: origin, content-type, accept');
header('Content-Type: application/json');

require __DIR__."/bootstrap.php";

if (!isset($_GET["mod"])) {
    header("HTTP/1.0 400 Bad Request");
    exit;
}
$module = $_GET["mod"];

$action = "index";
if (isset($_GET["a"])) {
    $action = $_GET["a"];
}


$storageType = $config->get("storage", "type", "files");
if ($storageType == "db") {
    $userStorage = new \App\Storage\Db\User($dbConnection);
} else {
    $userStorage = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
}


// Identification par clé API
$auth = new Auth\ApiKey($userStorage);
if (!$userAuthed = $auth->authenticate()) {
    header("HTTP/1.0 401 Unauthorized");
    exit;
}

// Si une action de modification de données est demandée, il faut que ce soit
// en POST.
if (in_array($action, array("create", "modify", "delete"))
    && $_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.0 400 Bad Request");
    exit;
}

$init = DOCUMENT_ROOT."/app/".$module."/init.php";
$script = DOCUMENT_ROOT."/app/api/".$module."/".$action.".php";

if (!is_file($script)) {
    header("HTTP/1.0 400 Bad Request");
    exit;
}

if (is_file($init)) {
    require $init;
}
$data = require $script;

if (empty($data)) {
    $data = array();
}
echo json_encode($data);
