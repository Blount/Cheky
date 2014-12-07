<?php

$storageType = $config->get("storage", "type");

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
        }
        $config->save();

        header("LOCATION: ?mod=admin&a=storage&success=1");
        exit;
    }
}