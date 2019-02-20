<?php

if (empty($_GET["id"]) || empty($_GET["tag"])) {
    header("LOCATION: ./?mod=annonce"); exit;
}

$ad = $storage->fetchById($_GET["id"]);

if ($tags = $ad->getTags()) {
    unset($tags[array_search($_GET["tag"], $tags)]);
    $ad->setTags($tags);

    $storage->save($ad);
}

header("LOCATION: ./?mod=annonce&a=view&id=".$ad->getId()."#tags");
exit;
