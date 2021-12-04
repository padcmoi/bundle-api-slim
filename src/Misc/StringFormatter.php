<?php

namespace Padcmoi\BundleApiSlim\Misc;

trait StringFormatter
{
    /**
     * Convertit un entier 0 ou 1 en boolean
     * et un boolean formaté en string
     * @param {*}
     *
     * @return {Boolean}
     */
    public static function convertStrToBool($str)
    {
        switch ($str) {
            case '0':
                $str = false;
                break;
            case '1':
                $str = true;
                break;
            case 'false':
                $str = false;
                break;
            case 'true':
                $str = true;
                break;
            default:
                $str = true;
                break;
        }
        return $str;
    }

    /**
     * Formatter au format slugify
     * @param {String}
     *
     * @return {String}
     */
    public static function slugify($string)
    {
        return self::stringFormatter($string, '-', true);
    }

    /**
     * Formatter au format snake case
     * @param {String}
     *
     * @return {String}
     */
    public static function snakeCase($string)
    {
        return self::stringFormatter($string, '_', true);
    }

    /**
     * Formatte un champ contenant des espaces en séparant avec un caractère
     * optionnel: possible de ne pas changer en minuscules
     *
     * @param {String} $text
     * @param {String} $separator
     * @param {Boolean} $lowercase_change
     *
     * @return {String}
     */
    protected static function stringFormatter(string $text, string $separator = '', bool $lowercase_change = false)
    {
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = $lowercase_change ? strtolower($clean) : $clean;
        $clean = preg_replace("/[\/_|+ -]+/", $separator, $clean);
        $clean = trim($clean, $separator);
        setlocale(LC_ALL, $oldLocale);
        return $clean;
    }

}