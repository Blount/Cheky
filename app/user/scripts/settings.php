<?php

$params = array(
    "notification" => $userAuthed->getOption("notification"),
    "unique_ads" => $userAuthed->getOption("unique_ads", false)
);

$errors = array();
$errorsTest = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $params = array_merge($params, array_intersect_key($_POST, $params));

    // test config Free Mobile
    foreach ($params["notification"] AS $section => $options) {
        if (is_array($options)) {
            $hasValue = false;
            foreach ($options AS $name => $value) {
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
            $sms = \Message\AdapterFactory::factory("freeMobile", $params["notification"]["freeMobile"]);
            try {
                $sms->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $errorsTest["freeMobile"] = "Erreur lors de l'envoi du SMS : (".$e->getCode().") ".$e->getMessage();
            }
        } elseif (!empty($_POST["testPushbullet"])) {
            if (empty($_POST["notification"]["pushbullet"]["token"])) {
                $errors["notification"]["pushbullet"]["token"] = "Veuillez renseigner la clé d'identification. ";
            } else {
                $sender = \Message\AdapterFactory::factory("pushbullet", $_POST["notification"]["pushbullet"]);
                try {
                    $sender->send("La notification Pushbullet est fonctionnelle");
                } catch (Exception $e) {
                    $errorsTest["pushbullet"] = "Erreur lors de l'envoi de la notification : (".$e->getCode().") ".$e->getMessage();
                }
            }
        } elseif (!empty($_POST["testOvh"])) {
            $sender = \Message\AdapterFactory::factory("SmsOvh", $params["notification"]["ovh"]);
            try {
                $sender->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $errorsTest["ovh"] = "Erreur lors de l'envoi du SMS : (".$e->getCode().") ".$e->getMessage();
            }
        } elseif (!empty($_POST["testNotifyMyAndroid"])) {
            if (empty($_POST["notification"]["notifymyandroid"]["token"])) {
                $errors["notification"]["notifymyandroid"]["token"] = "Veuillez renseigner la clé d'identification.";
            } else {
                $sender = \Message\AdapterFactory::factory("notifymyandroid", $_POST["notification"]["notifymyandroid"]);
                try {
                    $sender->send("La notification NotifyMyAndroid est fonctionnelle", array(
                        "title" => "Test alerte"
                    ));
                } catch (Exception $e) {
                    $errorsTest["notifymyandroid"] = "Erreur lors de l'envoi de la notification : (".$e->getCode().") ".$e->getMessage();
                }
            }
        } elseif (!empty($_POST["testPushover"])) {
            if (empty($_POST["notification"]["pushover"]["token"])) {
                $errors["notification"]["pushover"]["token"] = "Veuillez renseigner la clé application.";
            } elseif (empty($_POST["notification"]["pushover"]["user_key"])) {
                $errors["notification"]["pushover"]["user_key"] = "Veuillez renseigner la clé utilisateur.";
            } else {
                $sender = \Message\AdapterFactory::factory("pushover", $_POST["notification"]["pushover"]);
                try {
                    $sender->send("La notification Pushover est fonctionnelle");
                } catch (Exception $e) {
                    $errorsTest["pushover"] = "Erreur lors de l'envoi de la notification : (".$e->getCode().") ".$e->getMessage();
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
