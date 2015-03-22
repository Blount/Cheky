<?php
if (isset($_GET["id"])) {
    $alert = $storage->fetchById($_GET["id"]);
}
if (empty($alert)) {
    $alert = new App\Mail\Alert();
}

require_once "lib/Lbc/CategoryCollection.php";
$categoryCollection = new \Lbc\CategoryCollection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST AS $name => $value) {
        if (is_array($value)) {
            $_POST[$name] = array_map("trim", $_POST[$name]);
        } else {
            $_POST[$name] = trim($_POST[$name]);
        }
    }
    $alert->fromArray($_POST);
    if (empty($alert->send_mail) && empty($alert->send_sms_free_mobile)
        && empty($alert->send_sms_ovh) && empty($alert->send_pushbullet)) {
        $errors["send_type"] = "Vous devez sélectionner au moins un moyen de communication.";
    }
    if (empty($alert->email)) {
        $errors["email"] = "Ce champ est obligatoire.";
    }
    if (empty($alert->title)) {
        $errors["title"] = "Ce champ est obligatoire.";
    }
    if (empty($alert->price_min)) {
        $alert->price_min = -1;
    }
    if (empty($alert->price_max)) {
        $alert->price_max = -1;
    }
    if ($alert->price_min != (int)$alert->price_min) {
        $errors["price"] = "Valeur de \"prix min\" non valide. ";
    }
    if ($alert->price_max != (int)$alert->price_max) {
        $errors["price"] .= "Valeur de \"prix max\" non valide.";
    }
    if (!empty($_POST["price_strict"])) {
        $alert->price_strict = (int)(bool)$_POST["price_strict"];
    } else {
        $alert->price_strict = false;
    }
    $alert->group = !empty($_POST["group"])?trim($_POST["group"]):"";
    if (empty($alert->url)) {
        $errors["url"] = "Ce champ est obligatoire.";
    } else {
        $aUrl = parse_url($alert->url);
        if (!isset($aUrl["host"]) || $aUrl["host"] != "www.leboncoin.fr") {
            $errors["url"] = "Cette adresse ne semble pas valide.";
        } else {
            $alert->url = preg_replace("#o=[0-9]*&?#", "", $alert->url);
        }
    }
    $alert->interval = (int)$alert->interval;
    if ($alert->interval != (int)$alert->interval || $alert->interval < 0) {
        $errors["interval"] = "Cette valeur n'est pas valide.";
    }
    if (empty($errors)) {
        if (!empty($_POST["categories"])) {
            if (is_array($alert->categories)) {
                $alert->categories = implode(",", $_POST["categories"]);
            } else {
                $alert->categories = null;
            }
        } else {
            $alert->categories = null;
        }
        $storage->save($alert);
        header("LOCATION: ./?mod=mail"); exit;
    }
}