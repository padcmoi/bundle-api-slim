<?php

namespace Padcmoi\BundleApiSlim;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $mail;

    const SMTP_OPTIONS = [
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
    ];

    public function __construct()
    {
        if (!isset($_ENV['P_MAILER_HOSTNAME'])) {throw new \Exception('P_MAILER_HOSTNAME introuvable dans .env');}
        if (!isset($_ENV['P_MAILER_USERNAME'])) {throw new \Exception('P_MAILER_USERNAME introuvable dans .env');}
        if (!isset($_ENV['P_MAILER_PASSWORD'])) {throw new \Exception('P_MAILER_PASSWORD introuvable dans .env');}

        $port = isset($_ENV['P_MAILER_PORT']) ? $_ENV['P_MAILER_PORT'] : 587;
        $secure = isset($_ENV['P_MAILER_SECURE']) ? $_ENV['P_MAILER_SECURE'] : 'tls';
        $charset = isset($_ENV['P_MAILER_CHARSET']) ? $_ENV['P_MAILER_CHARSET'] : 'utf-8';

        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = $secure;
        $this->mail->SMTPOptions = self::SMTP_OPTIONS;
        $this->mail->CharSet = $charset;
        $this->mail->Host = $_ENV['P_MAILER_HOSTNAME'];
        $this->mail->Username = $_ENV['P_MAILER_USERNAME'];
        $this->mail->Password = $_ENV['P_MAILER_PASSWORD'];
        $this->mail->Port = intval($port);
    }

    /**
     * @param {String} $recipient
     * @param {String} $name
     *
     * @return $this
     */
    public function from(string $recipient, string $name = '')
    {
        $this->mail->setFrom($recipient, !$name ? $recipient : $name);
        return $this;
    }

    /**
     * @param {String} $recipient
     * @param {String} $name
     *
     * @return $this
     */
    public function to(string $recipient, string $name = '')
    {
        $this->mail->addAddress($recipient, !$name ? $recipient : $name);
        return $this;
    }

    /**
     * @param {Boolean} $state
     *
     * @return $this
     */
    public function setHTML(bool $state = true)
    {
        $this->mail->isHTML($state); //Set email format to HTML
        return $this;
    }

    /**
     * @param {String} $str
     *
     * @return $this
     */
    public function subject(string $str)
    {
        $this->mail->Subject = $str;
        return $this;
    }

    /**
     * @param {String} $str
     *
     * @return $this
     */
    public function body(string $str)
    {
        $this->mail->Body = $str;
        return $this;
    }

    /**
     * @param {String} $str
     *
     * @return $this
     */
    public function altBody(string $str)
    {
        $this->mail->AltBody = $str;
        return $this;
    }

    /**
     * @void
     */
    public function send()
    {
        try {
            $this->mail->send();
        } catch (\Throwable $th) {
            throw new \Throwable($th);
        }
    }
}