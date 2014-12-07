<?php

require dirname(__FILE__)."/bootstrap.php";

$module = "default";
if (isset($_GET["mod"])) {
    $module = $_GET["mod"];
}
$action = "index";
if (isset($_GET["a"])) {
    $action = $_GET["a"];
}

if (!$config->get("general", "version")) {
    if ($module != "install") {
        $module = "install";
    }
} elseif (isset($_GET["url"])) { // rendre compatible avec l'ancien système de flux RSS
    $module = "rss";
    $action = "refresh";
}

if ($module != "install") {
    $storageType = $config->get("storage", "type");
    if ($storageType == "db") {
        require_once DOCUMENT_ROOT."/app/models/Storage/Db/User.php";
        $userStorage = new \App\Storage\Db\User($dbConnection);
    } else {
        require_once DOCUMENT_ROOT."/app/models/Storage/File/User.php";
        $userStorage = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
    }

    // identification nécessaire
    if ($module == "rss" && $action == "refresh") {
        require_once "Auth/Session.php";
        require_once "Auth/Basic.php";
        $auth = new Auth\Session($userStorage);
        if (!$userAuthed = $auth->authenticate()) {
            $auth = new Auth\Basic($userStorage);
            if (!$userAuthed = $auth->authenticate()) {
                header('WWW-Authenticate: Basic realm="Identification"');
                header('HTTP/1.0 401 Unauthorized');
                echo "Non autorisé.";
                exit;
            }
        }
    } else {
        require_once "Auth/Session.php";
        $auth = new Auth\Session($userStorage);
        if (!$userAuthed = $auth->authenticate()) {
            $module = "default";
            $action = "login";
        }
    }
}

$init = DOCUMENT_ROOT."/app/".$module."/init.php";
$script = DOCUMENT_ROOT."/app/".$module."/scripts/".$action.".php";
$view = DOCUMENT_ROOT."/app/".$module."/views/".$action.".phtml";
$layout = DOCUMENT_ROOT."/app/".$module."/views/layout.phtml";

if (is_file($init)) {
    require $init;
}
if (is_file($script)) {
    require $script;
}
if (!is_file($layout)) {
    $layout = DOCUMENT_ROOT."/app/default/views/layout.phtml";
}

ob_start();
if (is_file($view)) {
    require $view;
}
$content = ob_get_clean();
if (isset($disableLayout) && $disableLayout == true) {
    echo $content;
} else {
    require $layout;
}



