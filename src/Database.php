<?php

namespace Padcmoi\BundleApiSlim;

class Database
{
    private static $charset = "utf8";

    /**
     * Créer une instance PDO
     *
     *
     * @return {Object}
     */
    protected static function instance()
    {
        if (!isset($_ENV['DB_HOSTNAME'])) {throw new \Exception('DB_HOSTNAME introuvable dans .env');}
        if (!isset($_ENV['DB_DATABASE'])) {throw new \Exception('DB_DATABASE introuvable dans .env');}
        if (!isset($_ENV['DB_USERNAME'])) {throw new \Exception('DB_USERNAME introuvable dans .env');}
        if (!isset($_ENV['DB_PASSWORD'])) {throw new \Exception('DB_PASSWORD introuvable dans .env');}

        return new \PDO(
            'mysql:host=' . $_ENV['DB_HOSTNAME'] . ';dbname=' . $_ENV['DB_DATABASE'] . ';charset=' . self::$charset . ';',
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );
    }

    /**
     * Instance et retourne celle-ci
     *
     * @return {Object}
     */
    public static function request()
    {
        return self::instance();
    }

    /**
     * Set charset
     * @param {String}
     *
     * @void
     */
    public static function charset(string $charset = "utf8")
    {
        switch (strtolower($charset)) {
            case 'utf8':
                self::$charset = "utf8";
                break;
            case 'utf8mb4':
                self::$charset = "utf8mb4";
                break;

            default:
                self::$charset = "utf8";
                break;
        }

        self::request()->exec('SET NAMES ' . self::$charset);
    }

    /**
     * Pour SQL requêtes:
     * SELECT
     * fetchAll pour lister tous les résultats possible, il est recommandé d'utiliser $limit
     *
     * @param {String} $request
     * @param {Array} $prepared
     * @param {Array} $options
     *
     * @return {Array}
     */
    public static function fetchAll(string $request, array $prepared = [], array $options = [])
    {
        $db = self::request();

        $req = $db->prepare($request);
        $req->execute($prepared);
        self::fetchMode($req, $options);
        $result = $req->fetchAll();
        $req->closeCursor();

        return $result;
    }

    /**
     * Pour SQL requêtes:
     * SELECT
     * Compte le nombre de résulats d'une requête
     *
     * @param {String} $request
     * @param {Array} $prepared
     * @param {Array} $options
     *
     * @return {Int}
     */
    public static function rowCount(string $request, array $prepared = [], array $options = [])
    {
        $db = self::request();

        $req = $db->prepare($request);
        $req->execute($prepared);
        self::fetchMode($req, $options);
        $result = $req->rowCount();
        $req->closeCursor();

        return $result;
    }

    /**
     * Pour SQL requêtes:
     * SELECT
     *
     * @param {String} $request
     * @param {Array} $prepared
     *
     * @return {Int}
     */
    public static function insert(string $request, array $prepared = [])
    {
        $db = self::request();

        $req = $db->prepare($request);
        $result = $req->execute($prepared);
        $lastInsertId = $db->lastInsertId();
        $req->closeCursor();

        return intval($lastInsertId);
    }

    /**
     * Pour SQL requêtes:
     * SELECT
     *
     * @param {String} $request
     * @param {Array} $prepared
     *
     * @return {Int}
     */
    public static function update(string $request, array $prepared = [])
    {
        return self::simpleRequest($request, $prepared);
    }

    /**
     * Pour SQL requêtes:
     * SELECT
     *
     * @param {String} $request
     * @param {Array} $prepared
     *
     * @return {Int}
     */
    public static function delete(string $request, array $prepared = [])
    {
        return self::simpleRequest($request, $prepared);
    }

    /**
     * Définit un FetchMode.
     *
     * @void
     */
    protected static function fetchMode(Object $db, array $options)
    {
        foreach ($options as $option) {
            switch ($option) {
                case 'fetch_named':
                    $db->setFetchMode(\PDO::FETCH_NAMED);
                    break;
            }
        }
    }

    /**
     * Pour executer la plupart des requêtes SQL
     *
     * @param {String} $request
     * @param {Array} $prepared
     *
     * @return {Int}
     */

    protected static function simpleRequest(string $request, array $prepared = [])
    {
        $db = self::request();

        $req = $db->prepare($request);
        $req->execute($prepared);
        $rows_affected = $req->rowCount();
        $req->closeCursor();

        return $rows_affected;
    }
}