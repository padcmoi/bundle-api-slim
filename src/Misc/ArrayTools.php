<?php

namespace Padcmoi\BundleApiSlim\Misc;

trait ArrayTools
{
    /**
     * Convertit dans un array tous les entiers 0 ou 1 en boolean
     * et les boolean formaté en string
     * @param {array}
     * @param {array}
     *
     * @return {array}
     */
    public static function convertInArrayValueToBool(array $array, array $keyToCheck = [])
    {
        return array_map(function ($obj) use ($keyToCheck) {
            foreach ($keyToCheck as $key) {
                if (isset($obj[$key])) {
                    $obj[$key] = self::convertStrToBool($obj[$key]);
                }
            }
            return $obj;
        }, $array);
    }

    /**
     * Trie un tableau de valeurs ou de clés valeurs
     * @param {array}
     * @param {string} key
     * @param {string} order
     * @param {bool}
     *
     * @return {array}
     */
    public static function arraySort(array $array, string $on, string $order = 'ASC', bool $withkey = false)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case 'ASC':
                    asort($sortable_array);
                    break;
                case 'DESC':
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                if ($withkey) {
                    $new_array[$k] = $array[$k];
                } else {
                    $new_array[] = $array[$k];
                }
            }
        }

        return $new_array;
    }
}