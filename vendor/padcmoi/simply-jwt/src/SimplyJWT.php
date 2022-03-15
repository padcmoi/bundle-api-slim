<?php
namespace Padcmoi\JWT;

class SimplyJWT
{
    use Utils;

    private static $init = false,
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT',
    ], $key = '', $expire = 3600;

    private function __construct()
    {}

    /**
     * Initialise/paramétrage
     *
     * @param {String} Clé privée
     * @param {String} Algorithme HS256, HS384, HS512
     * @param {Number} Expiration token
     *
     * @void
     */
    public static function init(string $key, string $alg = 'HS256', int $expire = 3600)
    {
        // définit la clé privée
        self::$key = $key;
        // définit l'algorithme
        self::$header['alg'] = strtoupper($alg);
        self::alg();
        // définit la clé privée
        self::$expire = intval($expire);

        self::$init = true;
    }

    /**
     * Génére un jeton contenant les informations du Payload
     * @param {array}
     *
     * @return {string}
     */
    public static function encode(array $payload = null)
    {
        if (!self::$init) {
            throw new \Exception("SimplyJWT::init() must be init", 1);
            exit;
        }

        if (!isset($payload['iat'])) {
            $payload['iat'] = time();
        }
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + intval(self::$expire);
        }

        // Crée la partie header & encode ce dernier
        $base64UrlHeader = self::base64UrlEncode(json_encode(self::$header));

        // Crée la partie Payload & encode ce dernier
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Génére une signature basée sur le header et le payload hashé avec la KEY + SHA1 UserAgent.
        $serverSignature = self::sign($base64UrlHeader, $base64UrlPayload);

        // Return JWT Token
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $serverSignature;
    }

    /**
     * Déserialise le jeton et retourne le Payload
     * @param {string}
     *
     * @return {array}
     */
    public static function decode(string $serializedToken)
    {
        if (!self::$init) {
            throw new \Exception("SimplyJWT::init() must be init", 1);
            exit;
        }

        // coupe le token en 3 séparé par des . / retourne un tableau vide en cas d'erreur de format
        $tokenParts = explode('.', $serializedToken);
        if (count($tokenParts) !== 3) {
            return [];
        }

        $header = self::convertStringToArray($tokenParts[0]);
        $payload = self::convertStringToArray($tokenParts[1]);

        // Verifie les clés du token JWT et affiche une erreur en cas de mauvais formatage.
        if (!is_array($header) || !is_array($payload) || !isset($header['alg']) || !isset($header['typ']) || !isset($payload['exp'])) {
            throw new \Exception("missing keys in SimplyJWT::decode()", 1);
            exit;
        }

        // Si le timestamp du jeton est supérieur à time() on retourne une exception
        if (time() > intval($payload['exp'])) {
            throw new \Exception("expired token SimplyJWT::decode()", 1);
            exit;
        }

        return $payload;
    }
}