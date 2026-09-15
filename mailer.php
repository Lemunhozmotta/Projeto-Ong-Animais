<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function enviarEmail($destinatario, $assunto, $corpo)
{
    // Detecta se está rodando em ambiente local
    $eh_localhost = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'])
        || strpos($_SERVER['SERVER_NAME'] ?? '', 'localhost') !== false;

    // Se NÃO for localhost, simula o envio (hospedagens gratuitas bloqueiam SMTP)
    if (!$eh_localhost) {
        // Log para debug (opcional)
        error_log("[APVAC] E-mail simulado para: $destinatario | Assunto: $assunto");
        return true;
    }

    $mail = new PHPMailer(true);

    try {
        // Carregar variáveis do .env
        $env = parse_ini_file(__DIR__ . '/.env');

        if (!$env) {
            error_log("[APVAC] Arquivo .env não encontrado!");
            return false;
        }

        // Configurações SMTP
        $mail->isSMTP();
        $mail->Host       = $env['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['MAIL_USER'];
        $mail->Password   = $env['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $env['MAIL_PORT'];
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 10; // Timeout de 10 segundos

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
        error_log("[APVAC] Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}