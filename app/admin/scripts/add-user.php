<?php
$user = new \App\User\User();
$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["username"]) || !trim($_POST["username"])) {
        $errors["username"] = "Veuillez indiquer un nom d'utilisateur.";
    } else {
        $user->setUsername(trim($_POST["username"]));
    }
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