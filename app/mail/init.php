<?php
if ($action != "check") {
    require_once DOCUMENT_ROOT."/app/models/Mail/Storage.php";
    $storage = new App\Mail\Storage(DOCUMENT_ROOT."/var/configs/".$auth->getUsername().".csv");
}