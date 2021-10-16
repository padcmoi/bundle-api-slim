<?php

namespace Padcmoi\BundleApiSlim\Misc;

trait RandomData
{

    /**
     * Génére un GUID
     *
     * @return {String}
     */
    public static function createGUID()
    {
        return sprintf('%04X-%04X-%04X-%04X-%04X', mt_rand(0, 32768), mt_rand(32768, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), time() - 1593200000);
    }

    /**
     * Génére une chaine de caractères personnalisés
     * @param {String}
     * @param {String}
     * @param {Number}
     *
     * @return {String}
     */
    private static $CHARS = "azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN0123456789";
    public static function stringGenerator($str = '', $specialChars = "!?.$", $size = 14)
    {
        $str = $str ? $str : self::$CHARS;

        $insertIndexSpecialChar = mt_rand(0, 10);
        $result = "";
        for ($i = 0; $i < $size; $i++) {
            if ($i === $insertIndexSpecialChar) {
                $result .= $specialChars[mt_rand(0, strlen($specialChars) - 1)];
            } else {
                $result .= $str[mt_rand(0, strlen($str) - 1)];
            }
        }
        return $result;
    }

}