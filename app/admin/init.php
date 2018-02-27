<?php

if (!$userAuthed->isAdmin()) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$security_issue = false;

$test_url = rtrim($config->get("general", "baseurl"), "/")."/var/config.ini";
$content = $client->request($test_url);

if (200 == $client->getRespondCode()
    || false !== strpos($content, "[general]")) {
    $security_issue = true;
}

unset($test_url, $content);
