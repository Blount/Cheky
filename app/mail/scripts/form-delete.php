<?php
if (!isset($_GET["id"])) {
    header("LOCATION: ./?mod=mail"); exit;
}
$alert = $storage->fetchById($_GET["id"]);
if (!$alert->id) {
    header("LOCATION: ./?mod=mail"); exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"]) && $_POST["id"] == $_GET["id"]) {
        $storage->delete($alert);
    }
    header("LOCATION: ./?mod=mail"); exit;
}