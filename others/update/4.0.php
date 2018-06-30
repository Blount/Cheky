<?php

require_once __DIR__."/update.php";

class Update_38 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert`
                ADD `error_count` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `ads_count`");

            $this->_dbConnection->query("ALTER TABLE `LBC_Alert`
                ADD `error` VARCHAR(255) DEFAULT NULL AFTER `error_count`");
        }
    }
}
