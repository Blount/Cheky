<?php

if (!isset($_GET["username"]) || !$user = $userStorage->fetchByUsername($_GET["username"])) {
    header("LOCATION: ?mod=admin&a=users");
    exit;
}

$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["password"])) {
        $errors["password"] = "Veuillez indiquer un mot de passe.";

    } elseif (empty($_POST["password"]) || $_POST["password"] != $_POST["confirmPassword"]) {
        $errors["confirmPassword"] = "Les deux mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        $user->setPassword(sha1($_POST["password"]));
        $userStorage->save($user);
        header("LOCATION: ?mod=admin&a=users");
        exit;
    }
}