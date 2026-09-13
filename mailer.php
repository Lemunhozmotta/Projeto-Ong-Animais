<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function enviarEmail($destinatario, $assunto, $corpo)
{
    $mail = new PHPMailer(true);

    try {
        // Carregar variáveis do .env
        $env = parse_ini_file(__DIR__ . '/.env');

        // Configurações SMTP
        $mail->isSMTP();
        $mail->Host       = $env['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['MAIL_USER'];
        $mail->Password   = $env['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $env['MAIL_PORT'];
        $mail->CharSet    = 'UTF-8';

        // Remetente
        $mail->setFrom($env['MAIL_FROM'], $env['MAIL_FROM_NAME']);

        // Destinatário
        $mail->addAddress($destinatario);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpo;
        $mail->AltBody = strip_tags($corpo);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}