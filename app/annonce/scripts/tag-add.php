<?php
if (!isset($_GET["id"])) {
    header("LOCATION: ./?mod=annonce"); exit;
}

$ad = $storage->fetchById($_GET["id"]);
if (!$ad) {
    header("LOCATION: ./?mod=annonce"); exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST"
    && ($tags = trim(isset($_POST["tags"]) ? $_POST["tags"] : ""))
) {
    $tags = array_map("trim", explode(",", $tags));
    $tags = array_unique(array_merge($ad->getTags(), $tags));

    $ad->setTags($tags);
    $storage->save($ad);
}

header("LOCATION: ./?mod=annonce&a=view&id=".$ad->getId()."#tags");
exit;
