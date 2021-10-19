<?php
namespace Padcmoi\BundleApiSlim;

class DataMerge
{
    private function __construct()
    {
        $this->data = array();
    }

    /**
     * Instance en singleton
     *
     * @return {instance}
     */
    private static $instance = null;
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Ajoute ou fusionne un objet ou un tableau
     * dans un tableau et retourne le total de ce tableau
     * @param {Array}
     *
     * @return {Array}
     */
    public function add(array $data)
    {
        return $this->data = array_merge($this->data, $data);
    }

    /**
     * Réinitialise le tableau
     *
     * @void
     */
    public function reset()
    {
        $this->data = array();
    }

    /**
     * Retourne le tableau en cours
     *
     * @return {Array}
     */
    public function build()
    {
        return $this->data;
    }

}