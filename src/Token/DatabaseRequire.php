<?php

namespace Padcmoi\BundleApiSlim\Token;

use Padcmoi\BundleApiSlim\Database;

class DatabaseRequire
{
    /**
     * Nom de la table contenant les jetons
     *
     * @return string
     */
    public static function customSqlTable()
    {
        return isset($_ENV['JWT_SQLTABLE']) ? $_ENV['JWT_SQLTABLE'] : '__tokens';
    }

    /**
     * Nom de la table contenant les jetons
     *
     * @return string
     */
    public static function foreignKey()
    {
        if (isset($_ENV['JWT_FK_TABLE']) && isset($_ENV['JWT_FK_USERID'])) {
            return ",
                KEY `fk_uid` (`uid`),
                CONSTRAINT `fk_token_uid` FOREIGN KEY (`uid`) REFERENCES `" . $_ENV['JWT_FK_TABLE'] . "` (`" . $_ENV['JWT_FK_USERID'] . "`) ON DELETE SET NULL ON UPDATE CASCADE
            ";
        } else {
            return "";
        }
    }

    /**
     * Vérifie si la table existe, crée la table sinon
     *
     * @void
     */
    public static function check()
    {
        $db = Database::request();

        $db->query("
        CREATE TABLE IF NOT EXISTS `" . self::customSqlTable() . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `header` enum('jwt','csrf') NOT NULL,
            `payload` varchar(64) NOT NULL,
            `uid` int(11) DEFAULT NULL,
            `ip` char(50) DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `not_before_renew` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
            `expire_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY (`payload`)
            " . self::foreignKey() . "
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}