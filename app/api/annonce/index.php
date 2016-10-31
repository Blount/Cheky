<?php

$ads = $storage->fetchAll();
$sort = isset($_GET["sort"]) ? ucfirst($_GET["sort"]) : "";
$order = isset($_GET["order"]) ? $_GET["order"] : "asc";

if ($sort && method_exists(new \App\Ad\Ad(), "get".$sort)) {
    setlocale(LC_CTYPE, "fr_FR.UTF-8");
    usort($ads, function ($ad1, $ad2) use ($sort) {
        $method = "get".$sort;
        $param1 = mb_strtolower($ad1->$method());
        $param2 = mb_strtolower($ad2->$method());
        if ($sort == "Title" && function_exists("iconv")) {
            $param1 = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $param1);
            $param2 = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $param2);
        }
        if ($param1 < $param2) {
            return -1;
        }
        if ($param1 > $param2) {
            return 1;
        }
        return 0;
    });
}
if ($order == "desc") {
    $ads = array_reverse($ads);
}

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