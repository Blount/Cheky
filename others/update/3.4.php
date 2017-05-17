<?php

require_once __DIR__."/update.php";

class Update_34 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_User`
                ADD `rss_key` CHAR(40) DEFAULT NULL UNIQUE AFTER `api_key`");

            $this->_dbConnection->query("ALTER TABLE `LBC_Alert`
                CHANGE `url` `url` TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL");

            $this->_dbConnection->query("ALTER TABLE `LBC_BackupAd` DROP `link_mobile`");
        }
    }
}
