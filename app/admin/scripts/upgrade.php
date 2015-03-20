<?php

require_once DOCUMENT_ROOT."/app/models/Updater.php";

$updater = new \App\Updater();
if ($url = $config->get("general", "url_version", "")) {
    $updater->setUrlVersion($url);
}
if ($url = $config->get("general", "url_archive", "")) {
    $updater->setUrlArchive($url);
}

if (isset($_POST["checkVersion"])) {
    unset($_SESSION["lbcLastVersion"], $_SESSION["lbcLastVersionTime"]);
    header("LOCATION: ?mod=admin&a=upgrade");
    exit;
}
if (empty($_SESSION["lbcLastVersion"]) || empty($_SESSION["lbcLastVersionTime"])) {
    try {
        $_SESSION["lbcLastVersion"] = $updater->getLastVersion();
        $_SESSION["lbcLastVersionTime"] = time();
    } catch (Exception $e) {

    }
}
$lastVersion = "";
if (!empty($_SESSION["lbcLastVersion"])) {
    $lastVersion = $_SESSION["lbcLastVersion"];
}

$currentVersion = $config->get("general", "version");
$upgradeStarted = version_compare($currentVersion, APPLICATION_VERSION, "<");
$upgradeAvailable = version_compare($currentVersion, $lastVersion, "<");

if ($upgradeStarted && !empty($_POST["upgrade"])) {
    $errors = array();
    $updater->update($currentVersion, $lastVersion);
    // mise à jour du numéro de version dans la config.
    $config->set("general", "version", $lastVersion);
    $config->save();
    header("LOCATION: ?mod=admin&a=upgrade");
    exit;

} elseif ($upgradeAvailable && !empty($_POST["upgrade"])) {
    $errors = array();
    try {
        $updater->installFiles($lastVersion);
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    $updater->update($currentVersion, $lastVersion);
    // mise à jour du numéro de version dans la config.
    $config->set("general", "version", $lastVersion);
    $config->save();
    header("LOCATION: ?mod=admin&a=upgrade");
    exit;
}