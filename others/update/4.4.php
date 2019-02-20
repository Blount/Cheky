<?php

require_once __DIR__."/update.php";

class Update_44 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_BackupAd` ADD `tags` TEXT DEFAULT NULL AFTER `comment`");
        }
    }
}
