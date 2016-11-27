<?php

if (!$userAuthed->getApiKey()) {
    $userAuthed->regenerateKey("api");
    $userStorage->save($userAuthed);
}

$params = array(
    "notification" => $userAuthed->getOption("notification"),
    "unique_ads" => $userAuthed->getOption("unique_ads", false),
    "api_key" => $userAuthed->getApiKey(),
    "addresses_mails" => $userAuthed->getOption("addresses_mails"),
);

require DOCUMENT_ROOT."/app/data/notifications.php";

$form_values = array(
    "api_key" => $params["api_key"],
    "unique_ads" => $params["unique_ads"],
    "addresses_mails" => $params["addresses_mails"],
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_values = array_intersect_key($_POST, $form_values);

    if (!empty($_POST["regenerate-apikey"])) {
        $userAuthed->regenerateKey("api");
    }

    $userAuthed->mergeOptions($form_values);
    $userStorage->save($userAuthed);
    $_SESSION["userSettingsSaved"] = true;
    header("LOCATION: ./?mod=user&a=settings"); exit;
}

$userSettingsSaved = isset($_SESSION["userSettingsSaved"]) && true === $_SESSION["userSettingsSaved"];
unset($_SESSION["userSettingsSaved"]);
