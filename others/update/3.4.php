<?php

require_once __DIR__."/update.php";

class Update_34 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert`
                CHANGE `url` `url` TEXT NOT NULL CHARACTER SET utf8 COLLATE utf8_bin");
        }
    }
}
