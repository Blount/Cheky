<?php

if (isset($_GET["id"])) {
    if ($alert = $storage->fetchById($_GET["id"])) {
        $alert->suspend = !$alert->suspend;
        $storage->save($alert);
    }
}
header("LOCATION: ./?mod=mail"); exit;