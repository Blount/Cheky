<?php

$errors = array();
$formErrors = array();
$messages = array();

if (!is_writable(DOCUMENT_ROOT."/var")) {
    $errors[] = "Il est nécessaire de pouvoir écrire dans le dossier 'var' (".DOCUMENT_ROOT."/var".").";
}

if (!$errors && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["password"])) {
        $formErrors["password"] = "Ce champ est obligatoire";
    } elseif (empty($_POST["confirmPassword"]) || $_POST["confirmPassword"] != $_POST["password"]) {
        $formErrors["confirmPassword"] = "Les mots de passe ne sont pas identiques.";
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
        $config->set("general", "version", $version);
        $config->save();

        require_once DOCUMENT_ROOT."/app/models/User/Storage.php";
        $userStorage = new App\User\Storage(DOCUMENT_ROOT."/var/users.db");

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