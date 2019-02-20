<?php

if (isset($_GET["sort"])) {
    $sort_keys = array(
        "title",
        "price",
        "city",
        "category",
        "author",
        "date",
        "zip_code",
        "date_created",
        "online",
    );
    if (in_array($_GET["sort"], $sort_keys)) {
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

if (isset($_GET["tag"])) {
    if (!isset($_SESSION["backupad"]["tags"])) {
        $_SESSION["backupad"]["tags"] = array();
    }

    if ("clean-filter" == $_GET["tag"]) {
        $_SESSION["backupad"]["tags"] = array();

    } elseif (in_array($_GET["tag"], $_SESSION["backupad"]["tags"])) {
        unset($_SESSION["backupad"]["tags"][array_search($_GET["tag"], $_SESSION["backupad"]["tags"])]);

    } else {
        $_SESSION["backupad"]["tags"][] = $_GET["tag"];
    }

    header("LOCATION: ?mod=annonce"); exit;
}

$sort = "date_created";
$order = "desc";
$filter_tags = array();

if (isset($_SESSION["backupad"]["sort"])) {
    $sort = $_SESSION["backupad"]["sort"];
}
if (isset($_SESSION["backupad"]["order"])) {
    $order = $_SESSION["backupad"]["order"];
}
if (isset($_SESSION["backupad"]["tags"])) {
    $filter_tags = $_SESSION["backupad"]["tags"];
    if (!is_array($filter_tags)) {
        $filter_tags = array();
    }
}

$ads = $storage->fetchAll($sort." ".$order);
$tags = $storage->fetchTags();

$real_ads = array();

foreach ($ads AS $ad) {
    if ($filter_tags) {
        if (!$ad_tags = $ad->getTags()) {
            continue;
        }

        if ($filter_tags == array_diff($filter_tags, $ad_tags)) {
            continue;
        }
    }

    $check_ad_online($ad);

    $real_ads[] = $ad;
}
$ads = $real_ads;
unset($real_ads);
