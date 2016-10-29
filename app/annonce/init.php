<?php
$storageType = $config->get("storage", "type", "files");
if ($storageType == "db") {
    $storage = new \App\Storage\Db\Ad($dbConnection, $userAuthed);
} else {
    $storage = new \App\Storage\File\Ad(DOCUMENT_ROOT."/var/configs/backup-ads-".$auth->getUsername().".csv");
}

$adPhoto = new App\Storage\AdPhoto($userAuthed);