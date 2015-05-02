<?php

if (!isset($_GET["url"])) {
    return;
}

$disableLayout = true;
$logFile = DOCUMENT_ROOT."/var/logs/rss.log";

require_once "FeedWriter/Item.php";
require_once "FeedWriter/Feed.php";
require_once "FeedWriter/RSS2.php";
use \FeedWriter\RSS2;

$aUrl = parse_url($_GET["url"]);

try {
    $parser = \AdService\ParserFactory::factory($_GET["url"]);
} catch (\AdService\Exception $e) {
     echo "Cette adresse ne semble pas valide.";
     exit;
}

$_GET["url"] = preg_replace("#o=[0-9]*&?#", "", $_GET["url"]);

// nettoyage cache
$files = array_diff(scandir(DOCUMENT_ROOT."/var/feeds"), array(".", ".."));
foreach ($files AS $file) {
    $file = DOCUMENT_ROOT."/var/feeds/".$file;
    $mtime = filemtime($file);
    if (($mtime + 20 * 60) < time()) {
        unlink($file);
    }
}

$logger = Logger::getLogger("main");

$id = sha1($_SERVER["REQUEST_URI"]);
$cache_filename = DOCUMENT_ROOT."/var/feeds/".$id.".xml";
if (is_file($cache_filename)) {
     readfile($cache_filename);
     return;
}

$params = $_GET;
if (isset($params["cities"])) {
    $params["cities"] = array_map("trim", explode("\n", mb_strtolower($params["cities"])));
}

$content = $client->request($_GET["url"]);

$filter = new \AdService\Filter($params);
$siteConfig = \AdService\SiteConfigFactory::factory($_GET["url"]);

$ads = $parser->process($content, $filter);

$title = $siteConfig->getOption("site_name");
$urlParams = parse_url($_GET["url"]);
if (!empty($urlParams["query"])) {
    parse_str($urlParams["query"], $aQuery);
    if (!empty($aQuery["q"])) {
        $title .= " - ".$aQuery["q"];
    }
}

$feeds = new RSS2;
$feeds->setTitle($siteConfig->getOption("site_name"));
$feeds->setLink($siteConfig->getOption("site_url"));
$feeds->setSelfLink(
    !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"?"https":"http".
    "://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]
);
$feeds->setDescription("Flux RSS de la recherche : ".htmlspecialchars($_GET["url"]));
$feeds->setChannelElement("language", "fr-FR");
// The date when this feed was lastly updated. The publication date is also set.
$feeds->setDate(date(DATE_RSS, time()));
$feeds->setChannelElement("pubDate", date(\DATE_RSS, strtotime("2013-04-06")));
$feeds->addGenerator();

if (count($ads)) {
    foreach ($ads AS $ad) {
        $item = $feeds->createNewItem();
        $item->setTitle($ad->getTitle());
        $item->setLink($ad->getLink());
        $item->setDescription(require DOCUMENT_ROOT."/app/rss/views/rss-ad.phtml");
        $item->setDate($ad->getDate());
        $item->setId(md5($ad->getId().$ad->getDate()));
        $feeds->addItem($item);
    }
}
$content = $feeds->generateFeed();
file_put_contents($cache_filename, $content);
echo $content;

