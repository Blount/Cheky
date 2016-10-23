<?php

if (isset($_GET["id"]) && $alert = $storage->fetchById($_GET["id"])) {
    $status = isset($_GET["s"])?$_GET["s"]:"";
    if (in_array($status, array("suspend", "send_mail", "send_sms_free_mobile",
        "send_sms_ovh", "send_pushbullet", "send_notifymyandroid",
        "send_pushover", "send_joaoappsjoin"))) {
        $alert->$status = !$alert->$status;
        $storage->save($alert);
    }
}
header("LOCATION: ./?mod=mail"); exit;