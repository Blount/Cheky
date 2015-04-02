<?php

require_once __DIR__."/update.php";

class Update_26 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_User` ADD `options` TEXT NULL DEFAULT NULL AFTER `password`");
            $users = $this->_dbConnection->query("SELECT * FROM `LBC_User`");
            while ($user = $users->fetch_object()) {
                $options = array();
                if (!empty($user->free_mobile_user) && !empty($user->free_mobile_key)) {
                    $options["notification"]["freeMobile"] = array(
                        "user" => $user->free_mobile_user,
                        "key" => $user->free_mobile_key
                    );
                } else {
                    $options["notification"]["freeMobile"] = false;
                }
                if (isset($user->unique_ads)) {
                    $options["unique_ads"] = (bool) $user->unique_ads;
                }
                $this->_dbConnection->query("UPDATE `LBC_User` SET
                    `options` = '".$this->_dbConnection->real_escape_string(json_encode($options))."'
                WHERE id = ".$user->id);
            }
            $this->_dbConnection->query("ALTER TABLE `LBC_User` DROP `free_mobile_user`");
            $this->_dbConnection->query("ALTER TABLE `LBC_User` DROP `free_mobile_key`");
            $this->_dbConnection->query("ALTER TABLE `LBC_User` DROP `unique_ads`");

            // mise à jour table Alert
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` CHANGE `send_sms` `send_sms_free_mobile` TINYINT(1) NOT NULL");
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `send_sms_ovh` TINYINT(1) NOT NULL AFTER `send_sms_free_mobile`");
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `send_pushbullet` TINYINT(1) NOT NULL AFTER `send_sms_ovh`");

        } elseif ("files" == $this->_storage) {
            $dir = DOCUMENT_ROOT.DS."var".DS."configs";
            if (is_dir($dir)) {
                foreach (scandir($dir) AS $file) {
                    if (preg_match("#user_.+\.json$#", $file)) {
                        $data = json_decode(trim(file_get_contents($dir.DS.$file)), true);
                        if (is_array($data)) {
                            $options = array();
                            if (!empty($data["free_mobile_user"]) && !empty($data["free_mobile_key"])) {
                                $options["notification"]["freeMobile"] = array(
                                    "user" => $data["free_mobile_user"],
                                    "key" => $data["free_mobile_key"]
                                );
                            } else {
                                $options["notification"]["freeMobile"] = false;
                            }
                            if (isset($data["unique_ads"])) {
                                $options["unique_ads"] = (bool) $data["unique_ads"];
                            }
                            file_put_contents($dir.DS.$file, json_encode($options));
                        }
                    } elseif (preg_match("#.+\.csv$#", $file)) {
                        // mise à jour fichier alert : send_sms" > "send_sms_free_mobile
                        file_put_contents($dir.DS.$file, str_replace(
                            "send_sms", "send_sms_free_mobile",
                            file_get_contents($dir.DS.$file)
                        ));
                    }
                }
            }
        }

        // suppression des fichiers obsolètes.
        if (is_file(DOCUMENT_ROOT.DS."app".DS."user".DS."scripts".DS."sms.php")) {
            unlink(DOCUMENT_ROOT.DS."app".DS."user".DS."scripts".DS."sms.php");
        }
        if (is_file(DOCUMENT_ROOT.DS."app".DS."user".DS."scripts".DS."sms.phtml")) {
            unlink(DOCUMENT_ROOT.DS."app".DS."user".DS."views".DS."sms.phtml");
        }
    }
}