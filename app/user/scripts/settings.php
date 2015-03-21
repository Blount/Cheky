<?php

$params = array(
    "notification" => array(
        "freeMobile" => $userAuthed->getOption("notification.freeMobile")
    )
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["notification"]["freeMobile"]["user"])) {
        $errors["notification"]["freeMobile"]["user"] = "Veuillez renseigner l'ID utilisateur. ";
    }
    if (empty($_POST["notification"]["freeMobile"]["key"])) {
        $errors["notification"]["freeMobile"]["key"] = "Veuillez renseigner la clé d'identification. ";
    }
    if (empty($errors)) {
        $params = array_merge($params, array_intersect_key($_POST, $params));
        if (!empty($_POST["test"])) {
            require_once "SMS/FreeMobile.php";
            $sms = new \SMS\FreeMobile();
            $sms->setKey($params["notification"]["freeMobile"]["key"])
                ->setUser($params["notification"]["freeMobile"]["user"]);
            try {
                $sms->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $error = "Erreur lors de l'envoi du SMS: (".$e->getCode().") ".$e->getMessage();
            }
        } else {
            $userAuthed->mergeOptions($params);
            $userStorage->save($userAuthed);
            header("LOCATION: ./?mod=user&a=settings"); exit;
        }
    }
}