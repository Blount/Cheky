<?php

if (isset($_GET["sort"])) {
    if (in_array($_GET["sort"], array("title", "email"))) {
        if (!isset($_SESSION["mail"]["sort"]) || $_SESSION["mail"]["sort"] != $_GET["sort"]) {
            $_SESSION["mail"]["sort"] = $_GET["sort"];
            $_SESSION["mail"]["order"] = "asc";
        } elseif (!isset($_SESSION["mail"]["order"]) || $_SESSION["mail"]["order"] == "desc") {
            $_SESSION["mail"]["order"] = "asc";
        } else {
            $_SESSION["mail"]["order"] = "desc";
        }
    }
    header("LOCATION: ?mod=mail"); exit;
}

$alerts = $storage->fetchAll();
$sort = "";
$order = isset($_SESSION["mail"]["order"])?$_SESSION["mail"]["order"]:"asc";

if (isset($_SESSION["mail"]["sort"])) {
    $sort = $_SESSION["mail"]["sort"];
    setlocale(LC_CTYPE, "fr_FR.UTF-8");
    usort($alerts, function ($alert1, $alert2) {
        $key = $_SESSION["mail"]["sort"];
        $param1 = mb_strtolower($alert1->$key);
        $param2 = mb_strtolower($alert2->$key);
        if ($key == "title" && function_exists("iconv")) {
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
if (isset($_SESSION["mail"]["order"]) && $_SESSION["mail"]["order"] == "desc") {
    $alerts = array_reverse($alerts);
}

// configuration du tableau d'affichage
$showCities = false;
$showPrice = false;

// trie les alertes par groupes
$alertsByGroup = array();
$groups = array();
foreach ($alerts AS $alert) {
    $group = $alert->group?$alert->group:"Sans groupe";
    $groups[] = $group;
    $alertsByGroup[$group][] = $alert;
    if (-1 != $alert->price_min || -1 != $alert->price_max) {
        $showPrice = true;
    }
    if ($alert->cities) {
        $showCities = true;
    }
}
$groups = array_unique($groups);
sort($groups);
if (in_array("Sans groupe", $groups)) {
    // met les alertes sans groupe à la fin.
    unset($groups[array_search("Sans groupe", $groups)]);
    $groups[] = "Sans groupe";
}

$notification["freeMobile"] = $userAuthed->hasSMSFreeMobile();
$notification["ovh"] = $userAuthed->hasSMSOvh();
$notification["pushbullet"] = $userAuthed->hasPushbullet();
$notification["notifymyandroid"] = $userAuthed->hasNotifyMyAndroid();











