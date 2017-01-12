<?php

$storageType = $config->get("storage", "type", "files");

$currentStorage = array(
    "type" => $config->get("storage", "type", "files"),
    "options" => $config->get("storage", "options", array())
);

$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["type"]) || !trim($_POST["type"])
        || !in_array($_POST["type"], array("files", "db"))) {
        $errors["type"] = "Type de stockage invalide.";
    }

    $currentStorage = array(
        "type" => isset($_POST["type"]) ? $_POST["type"] : $currentStorage["type"],
        "options" => array_merge($currentStorage["options"],
            isset($_POST["options"]) && is_array($_POST["options"]) ? $_POST["options"] : array())
    );

    if ($_POST["type"] == "db") {
        if (!isset($_POST["options"]["password"])) {
            $_POST["options"]["password"] = "";
        }
        if (empty($_POST["options"]["host"])) {
            $errors["host"] = "Nom d'hôte invalide.";
        }
        if (empty($_POST["options"]["user"])) {
            $errors["user"] = "Spécifiez un nom d'utilisateur.";
        }
        if (empty($_POST["options"]["dbname"])) {
            $errors["dbname"] = "Spécifiez une base de données.";
        }
        if (!empty($_POST["options"]["user"]) && !empty($_POST["options"]["dbname"])) {
            // test de connexion
            $dbConnection = new mysqli(
                $_POST["options"]["host"], $_POST["options"]["user"],
                $_POST["options"]["password"], $_POST["options"]["dbname"]);
            if ($dbConnection->connect_error) {
                $errors["host"] = "Connexion impossible à la base de données.";
            } else {
                $dbConnection->set_charset("utf8");
            }
        }
    }

    if (empty($errors)) {
        if ($_POST["type"] == "db") {
            $config->set("storage", "type", "db");
            $config->set("storage", "options", array(
                "host" => $_POST["options"]["host"],
                "user" => $_POST["options"]["user"],
                "password" => $_POST["options"]["password"],
                "dbname" => $_POST["options"]["dbname"],
            ));
        } else {
            $config->set("storage", "type", "files");

            if (!is_dir(DOCUMENT_ROOT."/var/configs")) {
                mkdir(DOCUMENT_ROOT."/var/configs");
            }
        }
        $config->save();

        if ($_POST["type"] == "db" && !empty($_POST["importtodb"])) {
            // installation de la base
            require DOCUMENT_ROOT."/others/install/schema.php";

            $userStorageDb = new \App\Storage\Db\User($dbConnection);

            $users = array();
            $usersDb = $userStorageDb->fetchAll(); // utilisateurs actuellement en BDD
            foreach ($usersDb AS $user) {
                $users[$user->getUsername()] = $user;
            }
            unset($usersDb);


            $userStorageFiles = new \App\Storage\File\User(DOCUMENT_ROOT."/var/users.db");
            $usersFiles = $userStorageFiles->fetchAll();
            foreach ($usersFiles AS $user) {
                if (!isset($users[$user->getUsername()])) {
                    $userStorageDb->save($user);
                }
            }

            $users = $userStorageDb->fetchAll();
            foreach ($users AS $user) {
                $file = DOCUMENT_ROOT."/var/configs/".$user->getUsername().".csv";
                if (!is_file($file)) {
                    continue;
                }
                $storageFiles = new \App\Storage\File\Alert($file);
                $storageDb = new \App\Storage\Db\Alert($userStorageDb->getDbConnection(), $user);
                $alerts = $storageFiles->fetchAll();
                foreach ($alerts AS $alert) {
                    $storageDb->save($alert, $forceinsert=true);
                }
            }
        }

        header("LOCATION: ?mod=admin&a=storage&success=1");
        exit;
    }
}