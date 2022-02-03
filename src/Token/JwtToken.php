<?php

namespace Padcmoi\BundleApiSlim\Token;

use Padcmoi\BundleApiSlim\Database;
use Padcmoi\JWT\SimplyJWT;

class JwtToken
{
    const EXPIRE = 3600;

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
     * Instance une seule fois SimplyJWT
     *
     * @void
     */
    private static $isInit = false;
    protected static function init()
    {
        if (!self::$isInit) {
            if (isset($_ENV['JWT_KEY'])) {
                $expire = isset($_ENV['JWT_EXPIRE']) ? intval($_ENV['JWT_EXPIRE']) : intval(self::EXPIRE);
                $alg = isset($_ENV['JWT_ALGORITHM']) ? $_ENV['JWT_ALGORITHM'] : 'HS512';
                SimplyJWT::init($_ENV['JWT_KEY'], $alg, $expire);
                self::$isInit = true;
            } else {
                throw new \Exception('JWT KEY introuvable dans .env');
            }
        }

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
            "DELETE FROM `" . self::customSqlTable() . "` WHERE `header` = 'jwt' AND TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) > :exp",
            array(":exp" => $expire)
        );
    }

    /**
     * Création d'un jeton et sauvegarde de ce dernier en DB
     * en cas d'echec, la methode est éxecuté de nouveau jusqu'à obtention
     * A surveiller! Boucle de la mort probable ou a refactorer
     *
     * @param {Int} $uid
     *
     * @return {String}
     */
    public static function create($uid = null)
    {

        DatabaseRequire::check();

        $expire = isset($_ENV['JWT_EXPIRE']) ? intval($_ENV['JWT_EXPIRE']) : intval(self::EXPIRE);
        $nbf = intval($expire - $expire * 25 / 100);

        self::init(); // Instanciation Singleton SimplyJWT
        $serializedToken = SimplyJWT::encode([
            "iss" => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'],
            "sub" => "access_token",
            "exp" => time() + $expire,
            "iat" => time(),
            "rnd" => md5(microtime(true)),
            "uid" => $uid,
        ]);

        $lastInsertId = Database::insert(
            "INSERT INTO `" . self::customSqlTable() . "` SET
                `payload` = md5(:payload),
                `header` = 'jwt',
                `uid` = :uid,
                `not_before_renew` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :nbf SECOND),
                `expire_at` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :exp SECOND)",
            array(':payload' => $serializedToken, ':uid' => $uid, ':nbf' => $nbf, ':exp' => $expire)
        );

        // A surveiller! Boucle de la mort probable ou a refactorer
        if ($lastInsertId == 0) {
            self::create($uid);
        }

        return $serializedToken;
    }

    /**
     * Supprime un jeton
     *
     * @param String $serializedToken
     *
     * @void
     */
    public static function delete(string $serializedToken)
    {
        DatabaseRequire::check();

        Database::delete(
            "DELETE FROM `" . self::customSqlTable() . "` WHERE `header` = 'jwt' AND `payload` = md5(:token) LIMIT 1",
            [":token" => $serializedToken]
        );
    }

    /**
     * Renouvelle le jeton à condition que la valeur NBF soit dépassée
     * sinon retourne l'ancien jeton comme étant valide
     *
     * @param {String} $serializedToken
     *
     * @return {String}
     */
    public static function tryRenew(string $serializedToken)
    {
        if (self::check($serializedToken, true)) {
            $newToken = self::create(self::getUid($serializedToken));
            self::delete($serializedToken); // Supprime l'ancien jeton
            return $newToken;
        } else {
            return $serializedToken;
        }
    }

    /**
     * Lit dans le payload du jeton l'identifiant utilisateur
     *
     * @param {String} $serializedToken
     *
     * @return {Int}
     */
    public static function getUid($serializedToken)
    {

        if (self::check($serializedToken)) {
            self::init(); // Instanciation Singleton SimplyJWT
            $payload = SimplyJWT::decode($serializedToken);
            return isset($payload['uid']) ? $payload['uid'] : -1;
        } else {
            return -1;
        }
    }

    /**
     * Vérifie si le jeton existe en BD, si il n'est pas expiré
     * Si $checkNbf est actif, vérifie
     * si le demandeur est en droit de demander un nouveau jeton
     *
     * @param {String} $serializedToken
     * @param {Boolean} $checkNbf
     *
     * @return {Boolean}
     */
    public static function check(string $serializedToken, bool $checkNbf = false)
    {
        self::purge(); // On purge avant les jetons expirés

        $add_nbf = $checkNbf ? ' AND TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `not_before_renew`) ) > 0 ' : '';

        $result = Database::rowCount(
            "SELECT * FROM `" . self::customSqlTable() . "` WHERE
                `payload` = MD5(:payload) AND
                `header` = 'jwt' AND
                TIME_TO_SEC( TIMEDIFF(CURRENT_TIMESTAMP() , `expire_at`) ) < 0
                " . $add_nbf . "
            LIMIT 1",
            array(':payload' => $serializedToken)
        );

        if ($result >= 1) {

            self::init(); // Instanciation Singleton SimplyJWT
            $payload = SimplyJWT::decode($serializedToken);

            if (!isset($payload['sub']) || $payload['sub'] != 'access_token') {
                // retourne http code car une manque une clé au payload et/ou son contenu
                http_response_code(403);exit;
            } else if (!isset($payload['iss']) || $payload['iss'] != $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) {
                // retourne http code car une manque une clé au payload et/ou son contenu
                http_response_code(403);exit;
            } else {
                return true;
            }

        } else {
            return false; // Invalide dans la base de données
        }
    }
}