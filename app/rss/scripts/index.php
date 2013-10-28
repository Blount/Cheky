<?php
$values = array(
    "url" => "", "price_min" => "", "price_max" => "", "price_strict" => false,
    "cities" => "", "categories" => array()
);

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
    $values = array_merge($values, $_POST);
    if (empty($values["url"])) {
        $errors["url"] = "Ce champ est obligatoire.";
    }
    if ($values["price_min"] && $values["price_min"] != (int)$values["price_min"]) {
        $errors["price"] = "Valeur de \"prix min\" non valide. ";
    }
    if ($values["price_max"] && $values["price_max"] != (int)$values["price_max"]) {
        $errors["price"] .= "Valeur de \"prix max\" non valide.";
    }
    if (empty($errors)) {
        $query = array("mod" => "rss", "a" => "refresh", "url" => $values["url"]);
        if (!empty($values["price_min"])) {
            $query["price_min"] = (int)$values["price_min"];
        }
        if (!empty($values["price_max"])) {
            $query["price_max"] = (int)$values["price_max"];
        }
        if (!empty($values["cities"])) {
            $query["cities"] = $values["cities"];
        }
        if (!empty($values["categories"]) && is_array($values["categories"])) {
            $query["categories"] = $values["categories"];
        }
        $query["price_strict"] = isset($values["price_strict"])?
            (int)(bool)$values["price_strict"]:0;
        header("LOCATION: ./?".http_build_query($query));
    }
}