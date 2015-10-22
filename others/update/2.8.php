<?php

require_once __DIR__."/update.php";

class Update_28 extends Update
{
    public function update()
    {
        // fichier inutile depuis 2.7
        if (is_file(DOCUMENT_ROOT."/lib/Lbc/Item.php")) {
            @unlink(DOCUMENT_ROOT."/lib/Lbc/Item.php");
        }
        if (is_file(DOCUMENT_ROOT."/lib/Lbc/Parser.php")) {
            @unlink(DOCUMENT_ROOT."/lib/Lbc/Parser.php");
        }

        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `send_notifymyandroid` TINYINT(1) NOT NULL AFTER `send_pushbullet`");
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `send_pushover` TINYINT(1) NOT NULL AFTER `send_notifymyandroid`");
        }
    }
}
