<?php

namespace Padcmoi\BundleApiSlim;

class SanitizeData
{
    private static $DATA, $IGNORE_FILTER = [], $IGNORE_KEY = [];

    /**
     * Ignore clés à néttoyer
     *
     * @void
     */
    public static function without(array $IGNORE_KEY = [])
    {
        self::$IGNORE_KEY = $IGNORE_KEY;

    }

    /**
     * Recupère les données de la requête client en cache
     * les données clientes sont deja nettoyé.
     *
     * return {Array}
     */
    public static function clean(bool $clean = true, array $ignore_filter = [])
    {
        self::$IGNORE_FILTER = $ignore_filter;
        return $clean ? self::cleanAllData() : self::requestMethod();
    }

    /**
     * Affiche les données traitées
     *
     * return {Array}
     */
    public static function show()
    {
        return isset(self::$DATA) ? self::$DATA : [];
    }

    /**
     * Réinitialise les données traitées
     *
     * @void
     */
    public static function reset()
    {
        unset(self::$DATA);
    }

    /**
     * Traite les champs envoyés par le client et nettoie.
     *
     * @param {String} $string - Texte à nettoyer.
     *
     * @return {String}
     */
    protected static function sanitize(string $string)
    {
        $string = self::searchKey(self::$IGNORE_FILTER, 'strip_tags') === -1 ? strip_tags($string) : $string;
        $string = self::searchKey(self::$IGNORE_FILTER, 'htmlspecialchars') === -1 ? htmlspecialchars($string) : $string;
        $string = self::searchKey(self::$IGNORE_FILTER, 'trim') === -1 ? trim($string) : $string;
        $string = self::searchKey(self::$IGNORE_FILTER, 'stripslashes') === -1 ? stripslashes($string) : $string;
        return $string;
    }

    /**
     * Retourne les méthods de requêtes
     *
     * @return {Array}
     */
    protected static function requestMethod()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET': // Lecture de données
                self::$DATA = $_GET;
                break;
            case 'POST': // Création/Ajout de données
                self::$DATA = $_POST;
                break;
            case 'PUT': // Mise à jour des données
                parse_str(file_get_contents('php://input'), $_PUT);
                self::$DATA = $_PUT;
                break;
            case 'DELETE': // Suppression de données
                parse_str(file_get_contents('php://input'), $_DELETE);
                self::$DATA = $_DELETE;
                break;
            default:
                self::$DATA = [];
        }
        return self::$DATA;
    }

    /**
     * Recupère les paramètres de la requete d'envoi et les nettoie pour être utilisé.
     * Stock les données de la requete cliente en cache afin d'eviter de nettoyer indéfiniment
     *
     * @return {Array}
     */
    protected static function cleanAllData()
    {
        if (!isset(self::$DATA)) {
            self::$DATA = [];
            $requestMethod = self::requestMethod();

            foreach ($requestMethod as $key => $value) {
                self::$DATA[$key] = self::searchKey(self::$IGNORE_KEY, $key) === -1 ? self::sanitize($value) : $value;
            }

            return self::$DATA;
        } else {
            return self::show();
        }
    }

    /**
     * Recherche la position dans un tableau.
     *
     * @param {Array} $array - Tableau à vérifier.
     * @param {String} $word - Mot à rechercher.
     *
     * @return {Int} - Clé de la position dans le tableau ou -1 pour non trouvé
     */
    protected static function searchKey(array $array, string $word)
    {
        foreach ($array as $key => $value) {
            if ($value === $word) {
                return $key;
            }
        }

        return -1;
    }
}