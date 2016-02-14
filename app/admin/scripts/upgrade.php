<?php

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
if ($_SERVER["REQUEST_METHOD"] == "POST" || empty($_SESSION["lbcLastVersion"]) || empty($_SESSION["lbcLastVersionTime"])) {
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

$errors = array();

$allow_update = true;
if (!is_writable(DOCUMENT_ROOT."/version.php")) {
    $allow_update = false;
    $errors[] = "Le fichier version.php est en lecture seule, la mise à jour automatique ne peut être effectuée.".
        "<br />Vérifiez que tous les fichiers soient accéssibles en écriture.";
} elseif (is_file(DOCUMENT_ROOT."/var/.lock")) {
    $allow_update = false;
    $errors[] = "Une vérification de nouvelle annonce est en cours, veuillez attendre la fin de celle-ci pour mettre à jour.";
}

$currentVersion = $config->get("general", "version");
$upgradeStarted = version_compare($currentVersion, APPLICATION_VERSION, "<");
$upgradeAvailable = version_compare($currentVersion, $lastVersion, "<");

if ($upgradeStarted && !empty($_POST["upgrade"])) {
    $updater->update($currentVersion, $lastVersion);
    // mise à jour du numéro de version dans la config.
    $config->set("general", "version", $lastVersion);
    $config->save();
    header("LOCATION: ?mod=admin&a=upgrade");
    exit;

} elseif ($allow_update && $upgradeAvailable && !empty($_POST["upgrade"])) {
    file_put_contents(DOCUMENT_ROOT."/var/.lock_update", time());
    try {
        $updater->installFiles($lastVersion);
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
    $version = require DOCUMENT_ROOT."/version.php";
    if ($version != $lastVersion) {
        $errors[] = "La mise à jour semble avoir échouée.";
    } else {
        $updater->update($currentVersion, $lastVersion);

        // mise à jour du numéro de version dans la config.
        $config->set("general", "version", $lastVersion);
        $config->save();
    }

    unlink(DOCUMENT_ROOT."/var/.lock_update");
    if (!$errors) {
        header("LOCATION: ?mod=admin&a=upgrade");
        exit;
    }
}