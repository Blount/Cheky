<?php

if (!isset($dbConnection))
    return;

$dbConnection->query("CREATE TABLE IF NOT EXISTS `LBC_User` (
    `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) COLLATE utf8_general_ci NOT NULL UNIQUE,
    `password` VARCHAR(40) NOT NULL,
    `free_mobile_user` VARCHAR(40) DEFAULT NULL,
    `free_mobile_key` VARCHAR(40) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin");

$dbConnection->query("CREATE TABLE IF NOT EXISTS `LBC_Alert` (
    `id` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idstr` CHAR(40) NOT NULL UNIQUE,
    `email` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
    `date_created` DATETIME NOT NULL,
    `title` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
    `url` VARCHAR(255) COLLATE utf8_bin NOT NULL,
    `interval` SMALLINT UNSIGNED NOT NULL,
    `time_last_ad` INTEGER UNSIGNED NOT NULL,
    `time_updated` INTEGER UNSIGNED NOT NULL,
    `price_min` INTEGER NOT NULL DEFAULT -1,
    `price_max` INTEGER NOT NULL DEFAULT -1,
    `price_strict` BOOLEAN NOT NULL,
    `cities` TEXT DEFAULT NULL,
    `suspend` BOOLEAN NOT NULL,
    `group` VARCHAR(255) COLLATE utf8_general_ci NOT NULL,
    `group_ads` BOOLEAN NOT NULL,
    `categories` TEXT DEFAULT NULL,
    `send_mail` BOOLEAN NOT NULL,
    `send_sms` BOOLEAN NOT NULL,
    `user_id` MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `LBCKey_Alert_User`
        FOREIGN KEY `user_id` (`user_id`)
        REFERENCES `LBC_User` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_bin");
