<?php

use AdService\SiteConfigFactory;

if (!isset($_GET["id"])) {
    header("LOCATION: ./?mod=annonce"); exit;
}

$ad = $storage->fetchById($_GET["id"]);
if (!$ad) {
    header("LOCATION: ./?mod=annonce"); exit;
}

try {
    $ad_config = SiteConfigFactory::factory($ad->getLink());
} catch (Exception $e) {

}

$check_ad_online($ad);
