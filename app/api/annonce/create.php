<?php
$ad = new App\Ad\Ad();

$link = "";

if (empty($_POST["link"])) {
    return array(
        "data" => $_POST,
        "errors" => array(
            "link" => "Ce champ est obligatoire."
        )
    );
}

$link = $_POST["link"];
try {
    $siteConfig = \AdService\SiteConfigFactory::factory($link);
    $parser = \AdService\ParserFactory::factory($link);
} catch (\Exception $e) {
    return array(
        "data" => $_POST,
        "errors" => array(
            "link" => "Cette adresse ne semble pas valide."
        )
    );
}

$ad = $parser->processAd(
    $client->request($link),
    parse_url($link, PHP_URL_SCHEME)
);

$ad_stored = $storage->fetchById($ad->getId());
if (!$ad_stored) {
    $ad_stored = new \App\Ad\Ad();
}

$ad_stored->setFromArray($ad->toArray());
$storage->save($ad_stored);

$adPhoto = new App\Storage\AdPhoto($userAuthed);
$adPhoto->import($ad_stored);

return $ad_stored->toArray();