<?php

require_once __DIR__."/update.php";

class Update_33 extends Update
{
    public function update()
    {
        if (!is_dir(DOCUMENT_ROOT."/static/media/annonce")) {
            mkdir(DOCUMENT_ROOT."/static/media/annonce", 0755, true);
        }

        if ("db" == $this->_storage) {
            $tables = array("LBC_User", "LBC_Alert");
            foreach ($tables AS $table) {
                $this->_dbConnection->set_charset("latin1");
                $rows = $this->_dbConnection->query("SELECT * FROM `".$table."`");
                $datas = array();
                while ($row = $rows->fetch_assoc()) {
                    $datas[] = $row;
                }

                $this->_dbConnection->set_charset("utf8");

                foreach ($datas AS $data) {
                    $id = $data["id"];
                    unset($data["id"]);
                    $sqlOptions = array();
                    foreach ($data AS $key => $value) {
                        if ($value === null) {
                            $value = "NULL";
                        } elseif (is_bool($value)) {
                            $value = (int) $value;
                        } elseif (!is_numeric($value)) {
                            $value = "'".$this->_dbConnection->real_escape_string($value)."'";
                        }
                        $sqlOptions[] = "`".$key."` = ".$value;
                    }
                    $this->_dbConnection->query("UPDATE `".$table."` SET ".
                        implode(", ", $sqlOptions).
                        " WHERE `id` = ".$id);
                }
            }

            $this->_dbConnection->query("CREATE TABLE IF NOT EXISTS `LBC_BackupAd` (
                `aid` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                `id` INTEGER UNSIGNED NOT NULL,
                `date_created` DATETIME NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `link` VARCHAR(255) NOT NULL,
                `link_mobile` VARCHAR(255) NOT NULL,
                `price` INTEGER UNSIGNED NOT NULL DEFAULT 0,
                `currency` VARCHAR(10) NOT NULL DEFAULT '€',
                `date` DATE NOT NULL,
                `category` VARCHAR(255) DEFAULT NULL,
                `country` VARCHAR(255) DEFAULT NULL,
                `zip_code` VARCHAR(10) DEFAULT NULL,
                `city` VARCHAR(255) DEFAULT NULL,
                `author` VARCHAR(255) DEFAULT NULL,
                `professional` BOOLEAN NOT NULL DEFAULT FALSE,
                `urgent` BOOLEAN NOT NULL DEFAULT FALSE,
                `photos` TEXT DEFAULT NULL,
                `properties` TEXT DEFAULT NULL,
                `description` TEXT NOT NULL,
                `user_id` MEDIUMINT UNSIGNED NOT NULL,
                PRIMARY KEY (`aid`),
                CONSTRAINT `LBCKey_BackupAd_User`
                    FOREIGN KEY `user_id` (`user_id`)
                    REFERENCES `LBC_User` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci");
        }
    }
}
