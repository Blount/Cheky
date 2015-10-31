<?php

/**
 * Rétrocompatibilité avec l'ancien système de génération de flux.
 * @deprecated
 */

$_GET["mod"] = "rss";
$_GET["a"] = "refresh";

require_once __DIR__."/../index.php";