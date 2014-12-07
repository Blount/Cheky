<?php
if ($action != "check") {
    $storageType = $config->get("storage", "type");
    if ($storageType == "db") {
        require_once DOCUMENT_ROOT."/app/models/Storage/Db/Alert.php";
        $storage = new \App\Storage\Db\Alert($dbConnection, $userAuthed);
    } else {
        require_once DOCUMENT_ROOT."/app/models/Storage/File/Alert.php";
        $storage = new \App\Storage\Db\Alert(DOCUMENT_ROOT."/var/configs/".$auth->getUsername().".csv");
    }
}