<?php

if (empty($_GET["s"])) {
    header("LOCATION: ./?mod=user&a=settings");
    exit;
}

switch ($_GET["s"]) {
    case "sms-free-mobile":
        $key = "freeMobile";
        break;
    case "sms-ovh":
        $key = "ovh";
        break;
    default:
        $key = $_GET["s"];
}

$notifications = $userAuthed->getOption("notification");
if (!isset($notifications[$key])) {
    header("LOCATION: ./?mod=user&a=settings");
    exit;
}

$notifications[$key]["active"] = false;

$userAuthed->mergeOptions(array(
    "notification" => $notifications
));
$userStorage->save($userAuthed);

header("LOCATION: ./?mod=user&a=settings");
exit;
