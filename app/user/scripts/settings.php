<?php

$params = array(
    "notification" => array(
        "freeMobile" => $userAuthed->getOption("notification.freeMobile"),
        "ovh" => $userAuthed->getOption("notification.ovh"),
        "pushbullet" => $userAuthed->getOption("notification.pushbullet"),
        "notifymyandroid" => $userAuthed->getOption("notification.notifymyandroid")
    ),
    "unique_ads" => $userAuthed->getOption("unique_ads", false)
);

$errors = array();
$errorsTest = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $params = array_merge($params, array_intersect_key($_POST, $params));

    // test config Free Mobile
    foreach (array("freeMobile", "ovh", "pushbullet", "notifymyandroid") AS $section) {
        if (isset($params["notification"][$section]) && is_array($params["notification"][$section])) {
            $hasValue = false;
            foreach ($params["notification"][$section] AS $name => $value) {
                if (empty($value)) {
                    $errors["notification"][$section][$name] = "Ce champ doit être renseigné.";
                } else {
                    $hasValue = true;
                }
            }
            if (!$hasValue) {
                unset($errors["notification"][$section]);
                $params["notification"][$section] = false;
            }
        }
    }
    if (empty($errors["notification"])) {
        unset($errors["notification"]);
    }

    if (empty($errors)) {
        if (!empty($_POST["testFreeMobile"])) {
            require_once "Message/SMS/FreeMobile.php";
            $sms = new \Message\SMS\FreeMobile($params["notification"]["freeMobile"]);
            try {
                $sms->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $errorsTest["freeMobile"] = "Erreur lors de l'envoi du SMS : (".$e->getCode().") ".$e->getMessage();
            }
        } elseif (!empty($_POST["testPushbullet"])) {
            if (empty($_POST["notification"]["pushbullet"]["token"])) {
                $errors["notification"]["pushbullet"]["token"] = "Veuillez renseigner la clé d'identification. ";
            } else {
                require_once "Message/Pushbullet.php";
                $sender = new \Message\Pushbullet($_POST["notification"]["pushbullet"]);
                try {
                    $sender->send("La notification Pushbullet est fonctionnelle");
                } catch (Exception $e) {
                    $errorsTest["pushbullet"] = "Erreur lors de l'envoi de la notification : (".$e->getCode().") ".$e->getMessage();
                }
            }
        } elseif (!empty($_POST["testOvh"])) {
            require "Message/SMS/Ovh.php";
            $sender = new \Message\SMS\Ovh($params["notification"]["ovh"]);
            try {
                $sender->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $errorsTest["ovh"] = "Erreur lors de l'envoi du SMS : (".$e->getCode().") ".$e->getMessage();
            }
        } elseif (!empty($_POST["testNotifyMyAndroid"])) {
            if (empty($_POST["notification"]["notifymyandroid"]["token"])) {
                $errors["notification"]["notifymyandroid"]["token"] = "Veuillez renseigner la clé d'identification. ";
            } else {
                require_once "Message/NotifyMyAndroid.php";
                $sender = new \Message\NotifyMyAndroid($_POST["notification"]["notifymyandroid"]);
                try {
                    $sender->send("La notification NotifyMyAndroid est fonctionnelle");
                } catch (Exception $e) {
                    $errorsTest["notifymyandroid"] = "Erreur lors de l'envoi de la notification : (".$e->getCode().") ".$e->getMessage();
                }
            }
        } else {
            $userAuthed->mergeOptions($params);
            $userStorage->save($userAuthed);
            $_SESSION["userSettingsSaved"] = true;
            header("LOCATION: ./?mod=user&a=settings"); exit;
        }
    }
}

$userSettingsSaved = isset($_SESSION["userSettingsSaved"]) && true === $_SESSION["userSettingsSaved"];
unset($_SESSION["userSettingsSaved"]);
