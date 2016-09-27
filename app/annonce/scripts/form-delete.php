<?php
if (!isset($_GET["id"])) {
    header("LOCATION: ./?mod=annonce"); exit;
}
$ad = $storage->fetchById($_GET["id"]);
if (!$ad) {
    header("LOCATION: ./?mod=annonce"); exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"]) && $_POST["id"] == $_GET["id"]) {
        $storage->delete($ad);
    }
    header("LOCATION: ./?mod=annonce"); exit;
}