<?php

$form_values = array(
    "user" => "",
    "key" => "",
);
$data_user = $userAuthed->getOption("notification.freeMobile");
if ($data_user && is_array($data_user)) {
    $form_values = array_merge($form_values, $data_user);
}

$errors = array();
$errorsTest = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["cancel-config"])) {
        header("LOCATION: ./?mod=user&a=settings");
        exit;
    }

    if (!empty($_POST["delete-config"])) {
        $userAuthed->mergeOptions(array(
            "notification" => array(
                "freeMobile" => false,
            ),
        ));
        $userStorage->save($userAuthed);
        $_SESSION["userSettingsSaved"] = true;
        header("LOCATION: ./?mod=user&a=settings");
        exit;
    }

    $form_values = array_intersect_key($_POST, $form_values);

    // Validation des champs
    foreach ($form_values AS $name => $value) {
        if (empty($value)) {
            $errors[$name] = "Ce champ doit être renseigné.";
        }
    }

    if (empty($errors)) {
        if (!empty($_POST["test-config"])) {
            $sms = \Message\AdapterFactory::factory("freeMobile", $form_values);
            try {
                $sms->send("La notification SMS est fonctionnelle.");
            } catch (Exception $e) {
                $errorsTest = "Erreur lors de l'envoi du SMS : (".$e->getCode().") ".$e->getMessage();
            }
        } else {
            $userAuthed->mergeOptions(array(
                "notification" => array(
                    "freeMobile" => array(
                        "user" => $form_values["user"],
                        "key" => $form_values["key"],
                        "active" => true,
                    ),
                ),
            ));
            $userStorage->save($userAuthed);
            $_SESSION["userSettingsSaved"] = true;
            header("LOCATION: ./?mod=user&a=settings");
            exit;
        }
    }
}
