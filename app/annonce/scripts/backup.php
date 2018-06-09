<?php

$url = !empty($_GET["aurl"]) ? $_GET["aurl"] : null;

$logger = Logger::getLogger("main");

$content = $client->request($url);

try {
    $parser = \AdService\ParserFactory::factory($url);
} catch (\AdService\Exception $e) {
    $logger->err($e->getMessage());
    return;
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
    $adPhoto->delete($ad);
}


if (!$ad_stored) {
    $ad_stored = new \App\Ad\Ad();
}

$ad_stored->setFromArray($ad->toArray());
$ad_stored->setOnline(true)
          ->setOnlineDateChecked(date("Y-m-d H:i:s"));
$storage->save($ad_stored);

$adPhoto->import($ad_stored);

header("LOCATION: ./?mod=annonce&a=view&id=".$ad->getId()); exit;