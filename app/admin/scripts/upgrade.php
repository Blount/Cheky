<?php

$errors = array();

$allowUpdate = false;
$upgradeStarted = false;
$upgradeAvailable = false;
$latestVersion = null;
$currentVersion = $config->get("general", "version", "");

if (!is_writable(DOCUMENT_ROOT."/version.php")) {
    $errors[] = "Le fichier version.php est en lecture seule, la mise à jour automatique ne peut être effectuée.".
        "<br />Vérifiez que tous les fichiers soient accéssibles en écriture (pas seulement le fichier version.php).";
    return;
}

if (is_file(DOCUMENT_ROOT."/var/.lock")) {
    $errors[] = "Une vérification de nouvelle annonce est en cours, veuillez attendre la fin de celle-ci pour mettre à jour.";
    return;
}

if (!$currentVersion) {
    $errors[] = "Version actuelle de Cheky non trouvée. Votre fichier config.ini est peut-être erroné.";
    return;

}

// Autorise la mise à jour à partir d'ici
$allowUpdate = true;

$updater = new \App\Updater(APPLICATION_VERSION);

// Date de la dernière vérification
$timeChecked = $updater->getTimeLastCheck();

if ($url = $config->get("general", "url_update", "")) {
    $updater->setUrl($url);
}

if (isset($_POST["force_check"])) {
    try {
        $updater->loadData(true);
        header("LOCATION: ?mod=admin&a=upgrade");
        exit;

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        return;
    }
}

try {
    $latestVersion = $updater->getLatestVersion();

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Permet de savoir si une mise à jour a été faite manuellement.
$upgradeStarted = version_compare($currentVersion, APPLICATION_VERSION, "<");

// Permet de savoir si une mise à jour est disponible
$upgradeAvailable = version_compare($currentVersion, $latestVersion, "<");

// Rafraichi la date de la dernière vérification
$timeChecked = $updater->getTimeLastCheck();

// La mise à jour des fichiers est faite, on termine la mise à jour
// si nécessaire (base de donénes, etc.)
if ($upgradeStarted && !empty($_POST["upgrade"])) {
    $updater->update($currentVersion, APPLICATION_VERSION);

    // mise à jour du numéro de version dans la config.
    $config->set("general", "version", APPLICATION_VERSION);
    $config->save();
    header("LOCATION: ?mod=admin&a=upgrade");
    exit;
}

// Exécute la mise à jour automatique
if ($allowUpdate && $upgradeAvailable && !empty($_POST["upgrade"])) {

    // Pose un verrou afin d'empêcher la tâche cron de se lancer
    file_put_contents(DOCUMENT_ROOT."/var/.lock_update", time());

    // Téléchargement et installation des fichiers
    try {
        $updater->installFiles($latestVersion);

        // Vérifie si la mise à jour a réussi
        $version = require DOCUMENT_ROOT."/version.php";
        if ($version != $latestVersion) {
            throw new Exception("La mise à jour semble avoir échouée.");
        }

        // Termine la mise à jour (base de donénes, etc.)
        $updater->update($currentVersion, $latestVersion);

        // mise à jour du numéro de version dans la config.
        $config->set("general", "version", $latestVersion);
        $config->save();

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    // Lève le verrou
    unlink(DOCUMENT_ROOT."/var/.lock_update");

    if (!$errors) {
        header("LOCATION: ?mod=admin&a=upgrade");
        exit;
    }
}

if ($errors) {
    $errors = array_map("nl2br", $errors);
}
