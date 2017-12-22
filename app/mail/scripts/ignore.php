<?php

$aurl = !empty($_GET["aurl"]) ? $_GET["aurl"] : null;
$error = false;

try {
    $parser = \AdService\ParserFactory::factory($aurl);
} catch (\AdService\Exception $e) {
    $error = "Cette annonce est invalide : ".htmlspecialchars($aurl);
}

if (!$error) {
    $ads_ignore = $userAuthed->getAdsIgnore();
    if (in_array($aurl, $ads_ignore)) {
        $error = "Vous ignorez déjà cette annonce.";
    }
}

if (!$error) {
    $ads_ignore[] = $aurl;
    $userAuthed->setAdsIgnore(array_unique($ads_ignore));

    $userStorage->save($userAuthed);
}
