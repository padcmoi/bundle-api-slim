<?php

namespace Padcmoi\BundleApiSlim\Misc;

trait ServerUtils
{
    /**
     * Retourne l'adresse URL complète
     * @param {Boolean} Avec l'affichage du script
     *
     * @return {string}
     */
    public static function getFullUrl(Bool $withScript = true)
    {
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
        if ($withScript) {
            $url .= $_SERVER['SCRIPT_NAME'];
        }
        return $url;
    }

    /**
     * Retourne la véritable adresse IP de l'utilisateur.
     * utile quand c'est derrière un CDN comme cloudflare
     * retournera l'adresse IP du parefeu au lieu de la vrai IP.
     *
     * @return {String}
     */
    public static function getIP()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}