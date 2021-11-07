<?php

namespace Padcmoi\BundleApiSlim\Token;

use Padcmoi\BundleApiSlim\Database;
use Padcmoi\BundleApiSlim\Misc;
use Padcmoi\BundleApiSlim\Token\DatabaseRequire;

class CsrfToken
{
    private static $WITH_CHECKIP = false;

    /**
     * Active la vérification d'IP
     * @param {Boolean} false - désactive la vérification des IP
     *
     * @void
     */
    public static function checkIP(bool $payload = true)
    {
        self::$WITH_CHECKIP = $payload;
    }

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
            "DELETE FROM `__tokens` WHERE `header` = 'csrf' AND TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) > 0"
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
            "INSERT INTO `__tokens` SET
                `payload` = :payload,
                `header` = 'csrf',
                `ip` = :ip,
                `expire_at` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 900 SECOND)",
            array(':payload' => $token, ':ip' => self::$WITH_CHECKIP ? Misc::getIP() : 'unchecked_ip')
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
            "UPDATE `__tokens` SET `expire_at` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 900 SECOND)
            WHERE `header` = 'csrf' AND `payload` = :payload AND `ip` = :ip LIMIT 1",
            array(':payload' => $token, ':ip' => self::$WITH_CHECKIP ? Misc::getIP() : 'unchecked_ip')
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
            "SELECT * FROM `__tokens` WHERE
                `header` = 'csrf' AND `payload` = :payload AND `ip` = :ip AND
                TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) < 0
                LIMIT 1",
            array(':payload' => $token, ':ip' => self::$WITH_CHECKIP ? Misc::getIP() : 'unchecked_ip')
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
    public static function delete(string $token)
    {
        self::purge();

        $rows_affected = Database::delete(
            "DELETE FROM `__tokens`
                WHERE `header` = 'csrf' AND `payload` = :payload AND `ip` = :ip AND
                TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) < 0
                LIMIT 1",
            array(':payload' => $token, ':ip' => self::$WITH_CHECKIP ? Misc::getIP() : 'unchecked_ip')
        );

        return $rows_affected >= 1 ? true : false;
    }

    /**
     * Doit fournir un jeton sinon on stop l'execution
     *
     * @param {String} $token
     *
     * @void
     */
    public static function protection(string $token)
    {
        self::purge();

        // Le jeton n'existe pas donc on refuse et on halt
        if (!self::check($token)) {
            exit;
        }

        // Jeton utilisé donc on le supprime
        self::delete($token);
    }
}