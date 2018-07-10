<?php

require_once __DIR__."/update.php";

class Update_41 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("UPDATE `LBC_Alert` SET
                    `url` = REPLACE(`url`, 'http://', 'https://'),
                    `error` = NULL,
                    `error_count` = 0
                WHERE `url` LIKE 'http://www.seloger.com/%'");
        }
    }
}
