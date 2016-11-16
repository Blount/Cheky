<?php

use AdService\SiteConfigFactory;

if (!isset($_GET["id"])) {
    header("LOCATION: ./?mod=annonce"); exit;
}

$ad = $storage->fetchById($_GET["id"]);
if (!$ad) {
    header("LOCATION: ./?mod=annonce"); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : "";
    $ad->setComment($comment);
    $storage->save($ad);

    header("LOCATION: ./?mod=annonce&a=view&id=".$ad->getId()."&update=1");
    exit;
}

try {
    $ad_config = SiteConfigFactory::factory($ad->getLink());
} catch (Exception $e) {

}