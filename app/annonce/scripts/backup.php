<?php

$url = !empty($_GET["aurl"]) ? $_GET["aurl"] : null;

$logger = Logger::getLogger("main");

$content = $client->request($url);

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
if ($ad_stored) {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        return;
    }

    // Supprime les photos
    foreach ($ad->getPhotos() AS $photo) {
        $filename = DOCUMENT_ROOT."/static/media/annonce/".$photo["local"];
        if (is_file($filename)) {
            unlink($filename);
        }
    }
}


if (!$ad_stored) {
    $ad_stored = new \App\BackupAd\Ad();
}

$ad_stored->setFromArray($ad->toArray());
$storage->save($ad_stored);
header("LOCATION: ./?mod=annonce&a=view&id=".$ad->getId()); exit;