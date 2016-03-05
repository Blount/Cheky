<?php

require_once __DIR__."/update.php";

class Update_31 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `max_id` INTEGER UNSIGNED NOT NULL DEFAULT '0' AFTER `last_id`");
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` CHANGE `last_id` `last_id` TEXT NULL DEFAULT NULL");
        }
    }
}
