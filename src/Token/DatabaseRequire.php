<?php

namespace Padcmoi\BundleApiSlim\Token;

use Padcmoi\BundleApiSlim\Database;

class DatabaseRequire
{
    /**
     * Vérifie si la table existe, crée la table sinon
     *
     * @void
     */
    public static function check()
    {
        $db = Database::request();

        $db->query("
        CREATE TABLE IF NOT EXISTS `token` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `header` enum('jwt','csrf') NOT NULL,
            `payload` varchar(300) NOT NULL,
            `uid` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `not_before_renew` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
            `expire_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY (`payload`),
            KEY `fk_uid` (`uid`),
            CONSTRAINT `fk_token_uid` FOREIGN KEY (`uid`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}
