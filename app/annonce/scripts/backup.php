<?php

$url = !empty($_GET["aurl"]) ? $_GET["aurl"] : null;

$logger = Logger::getLogger("main");

//$filename = DOCUMENT_ROOT."/var/tmp/annonce_".sha1($url).".html";
//if (!is_file($filename)) {
    $content = $client->request($url);
    //file_put_contents($filename, $content);
//} else {
    //$content = file_get_contents($filename);
//}

try {
    $parser = \AdService\ParserFactory::factory($url);
} catch (\AdService\Exception $e) {
    $logger->err($e->getMessage());
}

$ad = $parser->processAd(
    $content,
    parse_url($url, PHP_URL_SCHEME)
);

$ad_stored = $storage->fetchById($ad->getId());
if (!$ad_stored) {
    $ad_stored = new \App\BackupAd\Ad();
    $ad_stored->setFromArray($ad->toArray());
}

$storage->save($ad_stored);
header("LOCATION: ./?mod=annonce&a=view&id=".$ad->getId()); exit;