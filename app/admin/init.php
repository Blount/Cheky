<?php

if (!$userAuthed->isAdmin()) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$security_issue = false;

$test_url = rtrim($config->get("general", "baseurl"), "/")."/var/config.ini";
$connector = $app->getConnector($test_url);
$content = $connector->request();

if (200 == $connector->getRespondCode()
    || false !== strpos($content, "[general]")) {
    $security_issue = true;
}

unset($connector, $test_url, $content);
