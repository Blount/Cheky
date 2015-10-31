<?php
if ($action != "check") {
    $storageType = $config->get("storage", "type", "files");
    if ($storageType == "db") {
        $storage = new \App\Storage\Db\Alert($dbConnection, $userAuthed);
    } else {
        $storage = new \App\Storage\File\Alert(DOCUMENT_ROOT."/var/configs/".$auth->getUsername().".csv");
    }
}