<?php
$storageType = $config->get("storage", "type", "files");
if ($storageType == "db") {
    $storage = new \App\Storage\Db\BackupAd($dbConnection, $userAuthed);
} else {
    $storage = new \App\Storage\File\BackupAd(DOCUMENT_ROOT."/var/configs/backup-ads-".$auth->getUsername().".csv");
}