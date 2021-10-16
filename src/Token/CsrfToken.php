<?php

namespace Padcmoi\BundleApiSlim\Token;

use Padcmoi\BundleApiSlim\Database;

class CsrfToken
{
    /**
     * Purge les jetons obsolètes
     *
     * @void
     */
    public static function purge()
    {
        DatabaseRequire::check();

        $expire = isset($_ENV['JWT_EXPIRE']) ? intval($_ENV['JWT_EXPIRE']) : intval(self::EXPIRE);
        Database::delete(
            "DELETE FROM `token` WHERE `header` = 'csrf' AND TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) > 0"
        );
    }

    /**
     * TODO
     *
     * @return {String}
     */
    public static function create()
    {
        self::purge();

        $token = rtrim(strtr(base64_encode(bin2hex(random_bytes(5)) . time()), '+/', '-_'), '=');

        $lastInsertId = Database::insert(
            "INSERT INTO `token` SET
                `payload` = :payload,
                `header` = 'csrf',
                `expire_at` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 900 SECOND)",
            array(':payload' => $token)
        );

        // A surveiller! Boucle de la mort probable ou a refactorer
        if ($lastInsertId == 0) {
            self::create();
        }

        return $token;
    }

    /**
     * TODO
     *
     * @param {String} $token
     *
     * @return {Boolean}
     */
    public static function update(string $token)
    {
        self::purge();

        $rows_affected = Database::update(
            "UPDATE `token` SET `expire_at` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 900 SECOND)
            WHERE `header` = 'csrf' AND `payload` = :payload LIMIT 1",
            array(":payload" => $token)
        );

        return $rows_affected >= 1 ? true : false;
    }

    /**
     * TODO
     *
     * @param {String} $token
     *
     * @return {Boolean}
     */
    public static function check(string $token)
    {
        self::purge();

        $rows_affected = Database::rowCount(
            "SELECT * FROM `token` WHERE
                `header` = 'csrf' AND `payload` = :payload AND
                TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) < 0
                LIMIT 1",
            array(':payload' => $token)
        );

        return $rows_affected >= 1 ? true : false;
    }

}
