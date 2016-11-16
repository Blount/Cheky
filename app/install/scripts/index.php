<?php

$errors = array();
$formErrors = array();
$messages = array();

if (!function_exists("curl_init")) {
    $errors[] = "php-curl doit être installé pour continuer l'installation";
}

if (!class_exists("mysqli")) {
    $warnings["mysqli"] = "mysqli doit être installé pour utiliser le stockage en base de données";
}

if (!is_writable(DOCUMENT_ROOT."/var")) {
    $errors[] = "Il est nécessaire de pouvoir écrire dans le dossier 'var' (".DOCUMENT_ROOT."/var".").";
}

if (!$errors && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["password"])) {
        $formErrors["password"] = "Ce champ est obligatoire";
    } elseif (empty($_POST["confirmPassword"]) || $_POST["confirmPassword"] != $_POST["password"]) {
        $formErrors["confirmPassword"] = "Les mots de passe ne sont pas identiques.";
    }

    if (!empty($_POST["db"]["user"]) || !empty($_POST["db"]["dbname"])) {
        if (empty($_POST["db"]["host"])) {
            $formErrors["db"]["host"] = "Nom d'hôte invalide.";
        }
        if (empty($_POST["db"]["user"])) {
            $formErrors["db"]["user"] = "Spécifiez un nom d'utilisateur.";
        }
        if (empty($_POST["db"]["dbname"])) {
            $formErrors["db"]["dbname"] = "Spécifiez une base de données.";
        }
        if (!empty($_POST["db"]["user"]) && !empty($_POST["db"]["dbname"])) {
            // test de connexion
            $dbConnection = new mysqli(
                $_POST["db"]["host"], $_POST["db"]["user"],
                $_POST["db"]["password"], $_POST["db"]["dbname"]);
            if ($dbConnection->connect_error) {
                $formErrors["db"]["host"] = "Connexion impossible à la base de données.";
            }
        }
    }

    if (!$formErrors) {
        if (!is_dir(DOCUMENT_ROOT."/var/configs")) {
            mkdir(DOCUMENT_ROOT."/var/configs");
        }
        if (!is_dir(DOCUMENT_ROOT."/var/feeds")) {
            mkdir(DOCUMENT_ROOT."/var/feeds");
        }
        if (!is_dir(DOCUMENT_ROOT."/var/log")) {
            mkdir(DOCUMENT_ROOT."/var/log");
        }
        if (!is_dir(DOCUMENT_ROOT."/var/tmp")) {
            mkdir(DOCUMENT_ROOT."/var/tmp");
        }
        $config->set("general", "version", APPLICATION_VERSION);
        if (isset($dbConnection)) {
            $dbConnection->set_charset("utf8");
            $config->set("storage", "type", "db");
            $config->set("storage", "options", array(
                "host" => $_POST["db"]["host"],
                "user" => $_POST["db"]["user"],
                "password" => $_POST["db"]["password"],
                "dbname" => $_POST["db"]["dbname"],
            ));
        } else {
            $config->set("storage", "type", "files");
        }
        $config->save();

        $storageType = $config->get("storage", "type", "files");
        if ($storageType == "db") {
            // installation de la base
            require DOCUMENT_ROOT."/others/install/schema.php";

            $userStorage = new \App\Storage\Db\User($dbConnection);
        } else {
            $userStorage = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
        }

        // table utilisateurs
        $user = new \App\User\User(array(
            "username" => "admin",
            "password" => sha1($_POST["password"])
        ));
        $userStorage->save($user);

        header("LOCATION: ?mod=install&success=true");
        exit;
    }
}
