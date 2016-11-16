<?php
$username = "";
$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["username"]) || !trim($_POST["username"])) {
        $errors["password"] = "Nom d'utilisateur ou mot de passe incorrecte.";
    } else {
        $username = $_POST["username"];
    }
    if (empty($errors)) {
        $password = isset($_POST["password"])?$_POST["password"]:"";
        $auth->setUsername($username)
            ->setPassword(sha1($password));
        if ($auth->authenticate()) {
            if (isset($_GET["a"]) && $_GET["a"] == "login") {
                $redirect = "./";
            } else {
                $redirect = $_SERVER["REQUEST_URI"];
            }
            header("LOCATION: ".$redirect);
            exit;
        }
        $errors["password"] = "Nom d'utilisateur ou mot de passe incorrecte.";
    }
}