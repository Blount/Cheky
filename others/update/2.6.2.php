<?php

require_once __DIR__."/update.php";

class Update_262 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` ADD `last_id` INTEGER UNSIGNED NOT NULL DEFAULT '0' AFTER `send_pushbullet`");
        }
    }
}
