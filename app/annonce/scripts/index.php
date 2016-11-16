<?php

if (isset($_GET["sort"])) {
    if (in_array($_GET["sort"], array("title", "price", "city", "category", "author", "date", "zip_code", "date_created"))) {
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

$sort = "date_created";
$order = "desc";
if (isset($_SESSION["backupad"]["sort"])) {
    $sort = $_SESSION["backupad"]["sort"];
}
if (isset($_SESSION["backupad"]["order"])) {
    $order = $_SESSION["backupad"]["order"];
}

$ads = $storage->fetchAll($sort." ".$order);


