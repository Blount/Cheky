<?php

if (!$userAuthed->getApiKey()) {
    $userAuthed->setApiKey(
        sha1(
            str_repeat(
                uniqid($_SERVER["HTTP_HOST"], true),
                rand(10, 100)
            )
        )
    );
    $userStorage->save($userAuthed);
}

$params = array(
    "notification" => $userAuthed->getOption("notification"),
    "unique_ads" => $userAuthed->getOption("unique_ads", false),
    "api_key" => $userAuthed->getApiKey(),
);

$notifications = array(
    "sms-free-mobile" => array(
        "label" => "SMS - Free Mobile",
        "link" => "https://mobile.free.fr/moncompte/",
        "active" => !empty($params["notification"]["freeMobile"]["active"]),
    ),
    "sms-ovh" => array(
        "label" => "SMS - OVH Telecom",
        "cost" => "À partir de 0,07 € / SMS",
        "link" => "https://www.ovhtelecom.fr/sms/",
        "active" => !empty($params["notification"]["ovh"]["active"]),
    ),
    "pushbullet" => array(
        "label" => "Pushbullet",
        "link" => "https://www.pushbullet.com/",
        "active" => !empty($params["notification"]["pushbullet"]["active"]),
    ),
    "pushover" => array(
        "label" => "Pushover",
        "link" => "https://pushover.net/",
        "active" => !empty($params["notification"]["pushover"]["active"]),
    ),
    "notifymyandroid" => array(
        "label" => "NotifyMyAndroid",
        "cost" => "5 notifications / jour (illimité en premium)",
        "link" => "http://www.notifymyandroid.com/",
        "active" => !empty($params["notification"]["notifymyandroid"]["active"]),
    ),
    "joaoappsjoin" => array(
        "label" => "Joaoapps / Join",
        "link" => "https://joaoapps.com/join/",
        "active" => !empty($params["notification"]["joaoappsjoin"]["active"]),
    ),
);

$form_values = array(
    "api_key" => $params["api_key"],
    "unique_ads" => $params["unique_ads"],
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_values = array_intersect_key($_POST, $form_values);

    if (!empty($_POST["regenerate-apikey"])) {
        $userAuthed->setApiKey(
            sha1(
                str_repeat(
                    uniqid($_SERVER["HTTP_HOST"], true),
                    rand(10, 100)
                )
            )
        );
    }
    $userAuthed->mergeOptions($form_values);
    $userStorage->save($userAuthed);
    $_SESSION["userSettingsSaved"] = true;
    header("LOCATION: ./?mod=user&a=settings"); exit;
}

$userSettingsSaved = isset($_SESSION["userSettingsSaved"]) && true === $_SESSION["userSettingsSaved"];
unset($_SESSION["userSettingsSaved"]);
