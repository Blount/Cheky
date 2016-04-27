<?php

require_once __DIR__."/update.php";

class Update_32 extends Update
{
    public function update()
    {
        if ("db" == $this->_storage) {
            $this->_dbConnection->query("ALTER TABLE `LBC_Alert` CHANGE `last_id` `last_id` TEXT NULL DEFAULT NULL");
            $this->_dbConnection->query('UPDATE `LBC_Alert` SET `url` = REPLACE(`url`, "http://www.leboncoin.fr/", "https://www.leboncoin.fr/")');

        } elseif ("files" == $this->_storage) {
            $dir = DOCUMENT_ROOT.DS."var".DS."configs";
            if (is_dir($dir)) {
                $files = glob($dir.DS."*.csv");
                foreach ($files AS $file) {
                    file_put_contents(
                        $file,
                        str_replace(
                            "http://www.leboncoin.fr",
                            "https://www.leboncoin.fr",
                            file_get_contents($file)
                        )
                    );
                }
            }
        }
    }
}
