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
$sort = isset($_SESSION["backupad"]["sort"])?$_SESSION["backupad"]["sort"]:"dateCreated";
$order = isset($_SESSION["backupad"]["order"])?$_SESSION["backupad"]["order"]:"desc";

if ($sort && method_exists(new \App\Ad\Ad(), "get".ucfirst($sort))) {
    setlocale(LC_CTYPE, "fr_FR.UTF-8");
    usort($ads, function ($ad1, $ad2) use ($sort) {
        $method = "get".ucfirst($sort);
        $param1 = mb_strtolower($ad1->$method());
        $param2 = mb_strtolower($ad2->$method());
        if ($sort == "title" && function_exists("iconv")) {
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



