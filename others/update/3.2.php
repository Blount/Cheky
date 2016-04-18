<?php

require_once __DIR__."/update.php";

class Update_32 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` CHANGE `last_id` `last_id` TEXT NULL DEFAULT NULL");
        }
    }
}
