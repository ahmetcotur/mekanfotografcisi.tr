<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $enabled = false;

    public function __construct()
    {
        // Check if SMTP is configured
        if (\env('SMTP_HOST') && \env('SMTP_USER') && \env('SMTP_PASS')) {
            $this->enabled = true;
        }
    }

    /**
     * Send email using SMTP
     */
    public function send($to, $subject, $body, $replyTo = null)
    {
        if (!$this->enabled) {
            error_log("MailService: SMTP not configured. Falling back to native mail().");
            return $this->fallbackSend($to, $subject, $body, $replyTo);
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = \env('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = \env('SMTP_USER');
            $mail->Password = \env('SMTP_PASS');
            $mail->SMTPSecure = \env('SMTP_SECURE', 'ssl') === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) \env('SMTP_PORT', 465);
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom(\env('SMTP_FROM', 'info@mekanfotografcisi.tr'), \env('SMTP_NAME', 'Mekan Fotoğrafçısı'));
            $mail->addAddress($to);

            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            error_log("MailService Error: {$mail->ErrorInfo}");
            return $this->fallbackSend($to, $subject, $body, $replyTo);
        }
    }

    /**
     * Fallback to native PHP mail() if SMTP fails
     */
    private function fallbackSend($to, $subject, $body, $replyTo = null)
    {
        $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = "From: Mekan Fotografcisi <" . \env('SMTP_FROM', 'info@mekanfotografcisi.tr') . ">\r\n";
        if ($replyTo) {
            $headers .= "Reply-To: " . $replyTo . "\r\n";
        }
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return @mail($to, $encoded_subject, $body, $headers);
    }
}
