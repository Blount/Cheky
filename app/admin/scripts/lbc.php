<?php

$api_key = $config->get("lbc", "api_key", "ba0c2dad52b3ec");

$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["api_key"]) || !trim($_POST["api_key"])) {
        $api_key = "";
        $errors["api_key"] = "Ce champ est obligatoire.";
    }

    if (empty($errors)) {
        $config->set("lbc", "api_key", $_POST["api_key"]);
        $config->save();

        header("LOCATION: ?mod=admin&a=lbc&success=1");
        exit;
    }
}