<?php

require_once "SMS/FreeMobile.php";

$params = array(
    "free_mobile_user" => (string) $userAuthed->getOption("free_mobile_user"),
    "free_mobile_key" => (string) $userAuthed->getOption("free_mobile_key")
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["free_mobile_user"])) {
        $errors["free_mobile_user"] = "Veuillez renseigner l'ID utilisateur. ";
    }
    if (empty($_POST["free_mobile_key"])) {
        $errors["free_mobile_key"] = "Veuillez renseigner la clé d'identification. ";
    }
    if (empty($errors)) {
        $params = array_merge($params, array_intersect_key($_POST, $params));
        if (!empty($_POST["test"])) {
            $sms = new \SMS\FreeMobile();
            $sms->setKey($params["free_mobile_key"])
                ->setUser($params["free_mobile_user"]);
            try {
                $sms->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $error = "Erreur lors de l'envoi du SMS: (".$e->getCode().") ".$e->getMessage();
            }
        } else {
            $userAuthed->setOption("free_mobile_user", $params["free_mobile_user"])
                ->setOption("free_mobile_key", $params["free_mobile_key"]);
            $userStorage->save($userAuthed);
            header("LOCATION: ./?mod=user&a=sms"); exit;
        }
    }
}