<?php

$params = array(
    "free_mobile_user" => "", "free_mobile_key" => ""
);

$dir = DOCUMENT_ROOT.DS."var".DS."configs";
if (!is_dir($dir)) {
    mkdir($dir);
}
$filename = $dir.DS."user_".$auth->getUsername().".json";
if (is_file($filename)) {
    $data = json_decode(trim(file_get_contents($filename)), true);
    if ($data && is_array($data)) {
        $params = array_merge($params, array_intersect_key($data, $params));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["free_mobile_user"])) {
        $errors["free_mobile_user"] = "Veuillez renseigner l'ID utilisateur. ";
    }
    if (empty($_POST["free_mobile_key"])) {
        $errors["free_mobile_key"] = "Veuillez renseigner la clé d'identification. ";
    }
    if (empty($errors)) {
        $params = array_merge($params, array_intersect_key($_POST, $params));
        if (!empty($_POST["test"])) {
//             var_dump("https://smsapi.free-mobile.fr/sendmsg?".
//                 "user=".$params["free_mobile_user"].
//                 "&pass=".$params["free_mobile_key"].
//                 "&msg=Alerte"); exit;
            $ch = curl_init("https://smsapi.free-mobile.fr/sendmsg?".
                "user=".$params["free_mobile_user"].
                "&pass=".$params["free_mobile_key"].
                "&msg=Alerte");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            var_dump(curl_exec($ch)); exit;
        } else {
            file_put_contents($filename, json_encode($params));
            header("LOCATION: ./?mod=user&a=sms"); exit;
        }
    }
}