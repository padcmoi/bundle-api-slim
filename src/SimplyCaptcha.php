<?php

namespace Padcmoi\BundleApiSlim;

use Padcmoi\BundleApiSlim\Database;
use Padcmoi\BundleApiSlim\Misc;

class SimplyCaptcha
{
    private static $SETTING = [
        'number' => 6,
        'width' => 250,
        'height' => 50,
        'quality' => 70,
        'char_available' => '24689?tykjf',
        'font_type_path' => __DIR__ . '.\simplycaptcha.ttf',
    ];

    /**
     * Vérifie si la table existe, crée la table sinon
     *
     * @void
     */
    public static function dbStructure()
    {
        $db = Database::request();

        $db->query("
            CREATE TABLE IF NOT EXISTS `__simply_captcha` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `remote_ip` varchar(80) NOT NULL DEFAULT '',
                `picture` blob,
                `code` varchar(10) NOT NULL DEFAULT '',
                `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_test` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `remote_ip` (`remote_ip`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Purge les jetons obsolètes
     *
     * @void
     */
    public static function dbPurge()
    {
        self::dbStructure();

        Database::delete("DELETE FROM `__simply_captcha` WHERE DATEDIFF( CURRENT_TIMESTAMP, created) > 7;");
    }

    /**
     * Génére un captcha avec la librairie GD
     *
     * @return {Array}
     */
    public static function make()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'img');

        $image = imagecreatetruecolor(self::$SETTING['width'], self::$SETTING['height']);

        imagefilledrectangle($image, 0, 0, self::$SETTING['width'], self::$SETTING['height'], imagecolorallocate($image, 33, 33, 33));

        $random_char = strtolower(substr(str_shuffle(self::$SETTING['char_available']), 0, self::$SETTING['number']));

        $captcha_spaced = "";
        for ($i = 0; $i <= strlen($random_char); $i++) {
            $captcha_spaced .= substr($random_char, $i, 1) . " ";
        }

        if (file_exists(self::$SETTING['font_type_path'])) {
            for ($i = 0; $i <= 5; $i++) {
                $color = array(
                    [255, 0, 0],
                    [255, 255, 0],
                    [0, 255, 255],
                );
                $color_rnd = mt_rand(0, 2);

                imagerectangle($image, rand(1, self::$SETTING['width'] - 25), rand(1, self::$SETTING['height']), rand(1, self::$SETTING['width'] + 25), rand(1, self::$SETTING['height']), imagecolorallocate($image,
                    $color[$color_rnd][0], $color[$color_rnd][1], $color[$color_rnd][2])
                );
            }
            imagettftext($image, 22, 0, 5, (self::$SETTING['height'] / 1.5), imagecolorallocate($image, mt_rand(240, 255), mt_rand(120, 255), mt_rand(100, 255)), self::$SETTING['font_type_path'], $captcha_spaced);
            for ($i = 0; $i <= 5; $i++) {
                imagerectangle($image, rand(1, self::$SETTING['width'] - 25), rand(1, self::$SETTING['height']), rand(1, self::$SETTING['width'] + 25), rand(1, self::$SETTING['height']), imagecolorallocate($image,
                    mt_rand(100, 255), mt_rand(100, 255), mt_rand(0, 255))
                );
            }
        } else {
            imagestring($image, 5, 10, 0, $captcha_spaced, imagecolorallocate($image, 255, 255, 255)); // Si le true type ne fonctionne pas alors on affiche sans true type.
        }

        imagejpeg($image, $tmp, self::$SETTING['quality']);
        imagedestroy($image);

        $data = base64_encode(file_get_contents($tmp));
        @unlink($tmp);

        return array(
            'code' => $random_char,
            'base64' => 'data:image/jpeg;base64,' . $data,
        );

    }

    /**
     * Génére un captcha
     * Save ou met à jour le nouveau captcha dans la base de données
     *
     * @return {Array}
     */
    public static function create()
    {
        self::dbStructure();
        self::dbPurge();

        $captcha = self::make();

        $lastInsertId = Database::insert(
            "INSERT INTO `__simply_captcha` SET
                `remote_ip` = :remote_ip,
                `picture` = :picture,
                `code` = :code",
            array(
                ':remote_ip' => Misc::getIP(),
                ':picture' => $captcha['base64'],
                ':code' => $captcha['code'],
            )
        );

        if ($lastInsertId === 0) {
            Database::update(
                "UPDATE `__simply_captcha` SET
                    `picture` = :picture,
                    `code` = :code
                    WHERE `remote_ip` = :remote_ip LIMIT 1",
                array(
                    ':picture' => $captcha['base64'],
                    ':code' => $captcha['code'],
                    ':remote_ip' => Misc::getIP(),
                )
            );
        }

        return array(
            // 'code' => $captcha['code'], // pour debogage/test ne pas laisser ou commenter la ligne
            'picture' => $captcha['base64'],
        );
    }

    /**
     * Verifie en base de données si le code correspond avec l'IP
     *
     * @param {String}
     *
     * @return {Boolean}
     */
    public static function check(String $code)
    {
        self::dbStructure();
        self::dbPurge();

        $rowCount = Database::rowCount(
            "SELECT * FROM `__simply_captcha` WHERE `code` = :code AND `remote_ip` = :remote_ip LIMIT 1",
            array(':code' => strtolower($code), ':remote_ip' => Misc::getIP())
        );

        $result = $rowCount >= 1 ? true : false;

        if ($result) {
            Database::delete(
                "DELETE FROM `__simply_captcha` WHERE `code` = :code AND `remote_ip` = :remote_ip LIMIT 1",
                array(':code' => strtolower($code), ':remote_ip' => Misc::getIP())
            );
        }

        return $result;
    }
}