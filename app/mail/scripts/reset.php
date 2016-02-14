<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // place le marqueur de réinitialisation
    file_put_contents(DOCUMENT_ROOT."/var/tmp/reset_".$userAuthed->getId(), time());

    header("LOCATION: ./?mod=mail"); exit;
}