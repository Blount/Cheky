<?php

header("Content-Type: text/html; charset=utf-8");
header("Referrer-Policy: same-origin");

// PHP >= 5.4.0 nécessaire
if (-1 == version_compare(PHP_VERSION, "5.4")) {
    echo "Version PHP détectée : ".PHP_VERSION."<br />";
    echo "Version PHP minimal requise : 5.4<br />";
    exit(1);
}

require __DIR__."/bootstrap.php";

// Numéro de version actuel
try {
    $currentVersion = $config->get("general", "version");
} catch (Config_Lite_Exception $e) {
    echo "Le fichier de configuration 'var/config.ini' corrompu (numéro de version manquant).";
    exit(1);
}

// Type de stockage
try {
    $storageType = $config->get("storage", "type");
} catch (Config_Lite_Exception $e) {
    echo "Le fichier de configuration 'var/config.ini' corrompu (type de stockage indéfinit).";
    exit(1);
}

if ($storageType == "db") {
    $userStorage = new \App\Storage\Db\User($dbConnection);

} elseif ($storageType == "files") {
    $userStorage = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
}

$module = isset($_GET["mod"]) ? $_GET["mod"] : "default";
$action = isset($_GET["a"]) ? $_GET["a"] : "index";

// Si le numéro de version est vide, on passe à l'installation.
if (!$currentVersion) {
    $module = "install";

// Contrôle d'identification pour les flux RSS
} elseif ($module == "rss" && $action == "refresh") {
    // Identification par clé
    $rss_key = isset($_GET["key"]) ? $_GET["key"] : null;
    $username = isset($_GET["u"]) ? $_GET["u"] : null;
    if ($rss_key && $username) {
        $auth = new Auth\RssKey($userStorage);
        if (!$userAuthed = $auth->authenticate()) {
            header("HTTP/1.0 401 Unauthorized");
            exit;
        }

    /**
     * Identification par utilisateur/mot de passe via entête HTTP
     * @deprecated
     */
    } else {
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
    }

} else {
    $auth = new Auth\Session($userStorage);
    if (!$userAuthed = $auth->authenticate()) {
        $module = "default";
        $action = "login";
    }
}

// Vérifie si une mise à jour est en cours
$upgradeStarted = version_compare($currentVersion, APPLICATION_VERSION, "<");
if ($currentVersion && $upgradeStarted) {
    // Si utilisateur admin, redirige ver la page de mise à jour
    if ($userAuthed && $userAuthed->isAdmin()) {
        if ($module != "admin" || $action != "upgrade") {
            header("LOCATION: ./?mod=admin&a=upgrade");
            exit;
        }

    // Si utilisateur normal, message indiquant une mise à jour en cours
    } elseif ($action != "login") {
        require DOCUMENT_ROOT."/app/default/views/upgrade.phtml";
        return;
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



