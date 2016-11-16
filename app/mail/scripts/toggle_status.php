<?php

if (isset($_GET["id"]) && $alert = $storage->fetchById($_GET["id"])) {
    $status = isset($_GET["s"]) ? $_GET["s"] : "";
    $update = false;
    if ($status == "suspend") {
        $alert->$status = !$alert->$status;
        $update = true;
    } else {
        foreach ($data_notifications AS $name => $notification) {
            if ($status == $notification["form_name"]) {
                $alert->$status = !$alert->$status;
                $update = true;
                break;
            }
        }
    }

    if ($update) {
        $storage->save($alert);
    }
}

header("LOCATION: ./?mod=mail");
exit;
