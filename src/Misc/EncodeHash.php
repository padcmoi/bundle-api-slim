<?php

namespace Padcmoi\BundleApiSlim\Misc;

trait EncodeHash
{
    /**
     * PHP n'a pas de fonction base64UrlEncode, alors définissons celle qui
     * fait de la magie en remplaçant + par -, / par _ et = par ''.
     * De cette façon, nous pouvons passer la chaîne dans les URL sans
     * tout encodage d'URL.
     *
     * @param {String} $url
     *
     * @return {string}
     */
    public static function base64UrlEncode(string $url)
    {
        return str_replace(['+', '/', '=', '.'], '', base64_encode($url));
    }
}