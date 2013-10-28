<?php

$filename = DOCUMENT_ROOT."/var/log/info.log";

$lines = array();

if (is_file($filename)) {
    $lines = file($filename);
    if (count($lines) > 200) {
        $lines = array_slice($lines, count($lines)-200);
    }
}