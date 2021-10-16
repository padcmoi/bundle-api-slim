<?php

namespace Padcmoi\BundleApiSlim\Misc;

trait StringFormatter
{
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