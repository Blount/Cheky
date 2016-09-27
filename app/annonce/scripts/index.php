<?php

if (isset($_GET["sort"])) {
    if (in_array($_GET["sort"], array("title", "price", "city", "category", "author", "date", "zipCode", "dateCreated"))) {
        if (!isset($_SESSION["backupad"]["sort"]) || $_SESSION["backupad"]["sort"] != $_GET["sort"]) {
            $_SESSION["backupad"]["sort"] = $_GET["sort"];
            $_SESSION["backupad"]["order"] = "asc";
        } elseif (!isset($_SESSION["backupad"]["order"]) || $_SESSION["backupad"]["order"] == "desc") {
            $_SESSION["backupad"]["order"] = "asc";
        } else {
            $_SESSION["backupad"]["order"] = "desc";
        }
    }
    header("LOCATION: ?mod=annonce"); exit;
}

$ads = $storage->fetchAll();
$sort = "";
$order = isset($_SESSION["backupad"]["order"])?$_SESSION["backupad"]["order"]:"asc";

if (isset($_SESSION["backupad"]["sort"])
    && method_exists(new \App\BackupAd\Ad(), "get".ucfirst($_SESSION["backupad"]["sort"]))) {
    $sort = $_SESSION["backupad"]["sort"];
    setlocale(LC_CTYPE, "fr_FR.UTF-8");
    usort($ads, function ($ad1, $ad2) {
        $key = $_SESSION["backupad"]["sort"];
        $method = "get".ucfirst($key);
        $param1 = mb_strtolower($ad1->$method());
        $param2 = mb_strtolower($ad2->$method());
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
if (isset($_SESSION["backupad"]["order"]) && $_SESSION["backupad"]["order"] == "desc") {
    $ads = array_reverse($ads);
}



