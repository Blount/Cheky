<?php
if (empty($_POST["id"])) {
    return array(
        "data" => $_POST,
        "errors" => array(
            "id" => "Un ID doit être fourni"
        )
    );
}

$ad = $storage->fetchById($_POST["id"]);
if ($ad) {
    $storage->delete($ad);
    $adPhoto = new App\Storage\AdPhoto($userAuthed);
    $adPhoto->delete($ad);
}
