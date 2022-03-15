<?php
namespace Padcmoi\JWT;

trait Utils
{
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