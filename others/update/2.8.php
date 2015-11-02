<?php

require_once __DIR__."/update.php";

class Update_28 extends Update
{
    public function update()
    {
        $delete_files = array(
            "/lib/Lbc/Item.php", // fichier inutile depuis 2.7
            "/lib/Lbc/Parser.php", // fichier inutile depuis 2.7
            "/lib/AdService/Autoloader.php",
            "/lib/Message/SMS/FreeMobile.php",
            "/lib/Message/SMS/Ovh.php",
            "/lib/Message/Abstract.php",
            "/lib/Message/Pushbullet.php",
        );
        foreach ($delete_files AS $file) {
            if (is_file(DOCUMENT_ROOT.$file)) {
                @unlink(DOCUMENT_ROOT.$file);
            }
        }

        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `send_notifymyandroid` TINYINT(1) NOT NULL AFTER `send_pushbullet`");
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `send_pushover` TINYINT(1) NOT NULL AFTER `send_notifymyandroid`");
        }
    }
}
