<?php

require_once __DIR__."/update.php";

class Update_38 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_BackupAd`
                ADD `online` BOOLEAN NOT NULL DEFAULT 1 AFTER `date_created`,
                ADD `online_date_checked` DATETIME DEFAULT NULL AFTER `online`");
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert`
                ADD `ads_count` INTEGER UNSIGNED NOT NULL DEFAULT '0' AFTER `max_id`");
        }
    }
}
