<?php

namespace Padcmoi\BundleApiSlim;

class DotEnv
{
    /** Dossier du ficher .env */
    private static $DOTENVDIR = __DIR__ . '/../../../../',
    $DOTENVFILE = '.env',
    /** Clés requises pour le bon fonctionnement     */
    $KEYTOCHECK = ['DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'SHOW_ERRORS', 'JWT_KEY', 'JWT_EXPIRE'];

    /**
     * Charge le fichier environnement si il existe
     * si il n'existe pas, il sera crée puis chargé
     *
     * @param {String} Dossier ou se situe le .env
     *
     * @return {Boolean}
     */
    public static function load(String $dotEnvDiv = __DIR__ . '/../../../../')
    {
        self::$DOTENVDIR = $dotEnvDiv;

        // vérifie si le .env existe, cas échéant création du .env
        if (!self::check()) {
            self::create();
        }

        // charge la dépendance Dotenv cette dernière est celle qui charge le fichier .env
        $dotenv = \Dotenv\Dotenv::createImmutable(self::$DOTENVDIR);
        $dotenv->safeLoad();
        $dotenv->required(self::$KEYTOCHECK);

        return self::check();
    }

    /**
     * Vérifie si le fichier environnement existe
     *
     * @return {Boolean}
     */
    protected static function check()
    {
        return file_exists(self::$DOTENVDIR . '/' . self::$DOTENVFILE);
    }

    /**
     * Crée le fichier est ajoute les données
     *
     * @void
     */
    private static function create()
    {
        $file = fopen(self::$DOTENVDIR . '/' . self::$DOTENVFILE, "w") or die("Unable to dotenv file!");
        foreach (self::defaultData() as $key => $value) {
            fwrite($file, $value . "\n");
        }
        fclose($file);
    }

    /**
     * Les données à ajouter en Array pour chaque clé = 1 ligne
     *
     * @return {Array}
     */
    protected static function defaultData()
    {
        return [
            "SHOW_ERRORS=0\n",

            "JWT_KEY='" . base64_encode(bin2hex(random_bytes(32))) . "'",
            "JWT_EXPIRE=3600\n",

            "DB_HOSTNAME='localhost'",
            "DB_USERNAME='user'",
            "DB_PASSWORD='password'",
            "DB_DATABASE='base'\n",
        ];
    }

}
