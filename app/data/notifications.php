<?php

$notifications_enabled = array();
if ($userAuthed) {
    $notifications_enabled = $userAuthed->getNotificationsEnabled();
    $notifications_enabled["mail"] = array();
}

$data_notifications = array(
    "mail" => array(
        "list_label" => "Envoyer par email",
        "form_label" => "par email",
        "form_name" => "send_mail",
        "enabled" => true,
    ),
    "freemobile" => array(
        "label" => "SMS - Free Mobile",
        "link" => "https://mobile.free.fr/moncompte/",
        "list_label" => "SMS Free Mobile",
        "form_label" => "par SMS Free Mobile",
        "form_name" => "send_sms_free_mobile",
        "enabled" => isset($notifications_enabled["freeMobile"]),
    ),
    "ovh" => array(
        "label" => "SMS - OVH Telecom",
        "cost" => "À partir de 0,07 € / SMS",
        "link" => "https://www.ovhtelecom.fr/sms/",
        "list_label" => "SMS OVH",
        "form_label" => "par SMS OVH",
        "form_name" => "send_sms_ovh",
        "enabled" => isset($notifications_enabled["ovh"]),
    ),
    "pushbullet" => array(
        "label" => "Pushbullet",
        "link" => "https://www.pushbullet.com/",
        "list_label" => "Pushbullet",
        "form_label" => "par Pushbullet",
        "form_name" => "send_pushbullet",
        "enabled" => isset($notifications_enabled["pushbullet"]),
    ),
    "notifymyandroid" => array(
        "label" => "NotifyMyAndroid",
        "cost" => "5 notifications / jour (illimité en premium)",
        "link" => "http://www.notifymyandroid.com/",
        "list_label" => "NotityMyAndroid",
        "form_label" => "par NotityMyAndroid",
        "form_name" => "send_notifymyandroid",
        "enabled" => isset($notifications_enabled["notifymyandroid"]),
    ),
    "pushover" => array(
        "label" => "Pushover",
        "link" => "https://pushover.net/",
        "list_label" => "Pushover",
        "form_label" => "par Pushover",
        "form_name" => "send_pushover",
        "enabled" => isset($notifications_enabled["pushover"]),
    ),
    "joaoappsjoin" => array(
        "label" => "Joaoapps / Join",
        "link" => "https://joaoapps.com/join/",
        "list_label" => "Joaoapps / Join",
        "form_label" => "par Joaoapps / Join",
        "form_name" => "send_joaoappsjoin",
        "enabled" => isset($notifications_enabled["joaoappsjoin"]),
    ),
    "slack" => array(
        "label" => "Slack",
        "cost" => "Offre gratuite et premium",
        "link" => "https://slack.com",
        "list_label" => "Slack",
        "form_label" => "par Slack",
        "form_name" => "send_slack",
        "enabled" => isset($notifications_enabled["slack"]),
    ),
);
