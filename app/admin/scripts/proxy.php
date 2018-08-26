<?php
$errors = array();
$options = array(
    "proxy_ip" => $config->get("proxy", "ip", ""),
    "proxy_port" => $config->get("proxy", "port", ""),
    "proxy_user" => $config->get("proxy", "user", ""),
    "proxy_password" => $config->get("proxy", "password", "")
);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $options = array_merge(array(
        "proxy_ip" => "",
        "proxy_port" => "",
        "proxy_user" => ""
    ), array_map("trim", $_POST));
    if (isset($options["proxy_ip"])) {
        $options["proxy_ip"] = $options["proxy_ip"];
        if (isset($options["proxy_port"])) {
            $options["proxy_port"] = $options["proxy_port"];
        }
    }
    if (isset($_POST["testProxy"])) {
        $errors["test"] = array();

        $connector = $app->getConnector("https://portail.free.fr")
                         ->setProxyIp($options["proxy_ip"])
                         ->setProxyPort($options["proxy_port"])
                         ->setProxyUser($options["proxy_user"]);
        if (!empty($options["proxy_password"])) {
            $connector->setProxyPassword($options["proxy_password"]);
        }

        if (false === $connector->request()) {
            $errors["test"]["site"] = $connector->getError();
        }

        $connector = $app->getConnector("https://www.leboncoin.fr")
                         ->setProxyIp($options["proxy_ip"])
                         ->setProxyPort($options["proxy_port"])
                         ->setProxyUser($options["proxy_user"]);
        if (!empty($options["proxy_password"])) {
            $connector->setProxyPassword($options["proxy_password"]);
        }

        if (false === $connector->request()) {
            $errors["test"]["lbc"] = $connector->getError();
        }

    } else {
        $config->set("proxy", "ip", $options["proxy_ip"]);
        $config->set("proxy", "port", $options["proxy_port"]);
        $config->set("proxy", "user", $options["proxy_user"]);
        if (!empty($options["proxy_password"])) {
            $config->set("proxy", "password", $options["proxy_password"]);
        }
        $config->save();
        header("LOCATION: ?mod=admin&a=proxy");
        exit;
    }
}