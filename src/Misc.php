<?php

namespace Padcmoi\BundleApiSlim;

class Misc
{

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

    /**
     * Retourne l'adresse URL complète
     *
     * @return {string}
     */
    public static function getFullUrl()
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'];
    }

}