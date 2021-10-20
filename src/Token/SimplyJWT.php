<?php
namespace Padcmoi\BundleApiSlim\Token;

class SimplyJWT
{
    private static $instance = null,
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT',
    ], $key = '', $expire = 3600;

    private function __construct()
    {}

    /**
     * Instance en singleton la class
     *
     * @param {String} Clé privée
     * @param {String} Algorithme HS256, HS384, HS512
     * @param {Number} Expiration token
     *
     * @return {class::instance}
     */
    public static function init(string $key, string $alg = 'HS256', int $expire = 3600)
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        // définit la clé privée
        self::$key = $key;
        // définit l'algorithme
        self::$header['alg'] = strtoupper($alg);
        self::alg();
        // définit la clé privée
        self::$expire = intval($expire);

        return self::$instance;
    }

    /**
     * Génére un jeton contenant les informations du Payload
     * @param {array}
     *
     * @return {string}
     */
    public static function encode(array $payload = null)
    {
        if (!self::$instance) {
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
        if (!self::$instance) {
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

    /**
     * Génére une signature basée sur le header et le payload
     * @param {string}
     * @param {string}
     *
     * @return {string}
     */
    private static function sign(string $header, string $payload)
    {
        $signature = hash_hmac(self::alg(), $header . '.' . $payload, self::$key, true);
        return self::base64UrlEncode($signature);
    }

    /**
     * Convertit un type string en tableau associatif
     * et crée un tableau vide en cas d'erreur.
     * @param {string}
     *
     * @return {array}
     */
    private static function convertStringToArray(string $string)
    {
        $arrayProbably = json_decode(base64_decode($string), true);
        return is_array($arrayProbably) ? $arrayProbably : array();
    }

    /**
     * PHP n'a pas de fonction base64UrlEncode, alors définissons celle qui
     * fait de la magie en remplaçant + par -, / par _ et = par ''.
     * De cette façon, nous pouvons passer la chaîne dans les URL sans
     * tout encodage d'URL.
     * @param {string}
     *
     * @return {string}
     */
    private static function base64UrlEncode(string $url)
    {
        return str_replace(['+', '/', '=', '.'], '', base64_encode($url));
    }

    /**
     * Convertit/définit le hashage pour hash_hmac
     *
     * @return {string}
     */
    private static function alg()
    {
        $alg = 'sha256';
        switch (strtoupper(self::$header['alg'])) {
            case 'HS256':
                $alg = 'sha256';
                break;
            case 'HS384':
                $alg = 'sha384';
                break;
            case 'HS512':
                $alg = 'sha512';
                break;
            default:
                self::$header['alg'] = 'HS256';
        }
        return $alg;
    }
}