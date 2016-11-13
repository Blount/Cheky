<?php
$ad = new App\Ad\Ad();

$link = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["link"])) {
        $errors["link"] = "Ce champ est obligatoire.";
    } else {
        $link = $_POST["link"];
        try {
            $siteConfig = \AdService\SiteConfigFactory::factory($link);
            $parser = \AdService\ParserFactory::factory($link);
        } catch (\Exception $e) {
            $errors["link"] = "Cette adresse ne semble pas valide.";
        }
    }
    if (empty($errors)) {
        $ad = $parser->processAd(
            $client->request($link),
            parse_url($link, PHP_URL_SCHEME)
        );
        if (!$ad) {
            $errors["link"] = "Impossible de sauvegarder l'annonce (annonce hors ligne ou format des données invalides).";
        }
    }
    if (empty($errors) && !empty($ad)) {
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

        $adPhoto->import($ad_stored);

        header("LOCATION: ./?mod=annonce");
        exit;
    }
}