<?php

$order_by = null;
$sort = isset($_GET["sort"]) ? $_GET["sort"] : "";
$order = isset($_GET["order"]) ? $_GET["order"] : "asc";

if ($sort) {
    $order_by = $sort." ".$order;
}

$ads = $storage->fetchAll($order_by);

$baseurl = $config->get("general", "baseurl", "");
$adPhoto = new App\Storage\AdPhoto($userAuthed);
$return = array();
foreach ($ads AS $ad) {
    $params = $ad->toArray();
    foreach ($params["photos"] AS $i => $photo) {
        $params["photos"][$i]["local"] = $baseurl.$adPhoto->getPublicDestination($photo["local"]);
    }
    $return[$ad->getId()] = $params;
}

return $return;