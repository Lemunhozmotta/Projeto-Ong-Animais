<?php
session_start();
include "conexao.php";
require_once "mailer.php";

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $mensagem = "Informe seu e-mail!";
        $tipo_mensagem = "erro";
    } else {
        // Buscar usuário pelo email
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        if ($usuario) {
            // Gerar token único
            $token = bin2hex(random_bytes(32));
            $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Salvar token no banco
            $update_sql = "UPDATE usuarios SET token_recuperacao = ?, expira_token = ? WHERE id_usuario = ?";
            $update_stmt = $conexao->prepare($update_sql);
            $update_stmt->bind_param("ssi", $token, $expiracao, $usuario['id_usuario']);

            if ($update_stmt->execute()) {
                // Montar link de recuperação
                $link = "http://localhost/Projeto-Ong-Animais/redefinir_senha.php?token=" . $token;

                // Corpo do e-mail
                $corpo = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #1a1a2e; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                        .btn { display: inline-block; background: #008b8b; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                        .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🐾 APVAC</h1>
                        </div>
                        <div class='content'>
                            <h2>Olá, " . htmlspecialchars($usuario['nome']) . "!</h2>
                            <p>Recebemos uma solicitação para redefinir sua senha.</p>
                            <p>Clique no botão abaixo para criar uma nova senha:</p>
                            <p style='text-align: center;'>
                                <a href='$link' class='btn'>Redefinir Minha Senha</a>
                            </p>
                            <p><strong>Este link expira em 1 hora.</strong></p>
                            <p>Se você não solicitou esta alteração, ignore este e-mail.</p>
                        </div>
                        <div class='footer'>
                            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
                        </div>
                    </div>
                </body>
                </html>
                ";

                // Enviar e-mail
                if (enviarEmail($email, "Redefinição de Senha - APVAC", $corpo)) {
                    $mensagem = "✅ Enviamos um link de recuperação para seu e-mail! Verifique sua caixa de entrada.";
                    $tipo_mensagem = "sucesso";
                } else {
                    $mensagem = "❌ Erro ao enviar e-mail. Tente novamente mais tarde.";
                    $tipo_mensagem = "erro";
                }
            } else {
                $mensagem = "❌ Erro ao processar solicitação.";
                $tipo_mensagem = "erro";
            }
        } else {
            // Por segurança, não revelamos se o e-mail existe ou não
            $mensagem = "✅ Se este e-mail estiver cadastrado, você receberá um link de recuperação.";
            $tipo_mensagem = "sucesso";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_recuperar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Recuperar Senha - APVAC</title>
</head>

<body>

    <div class="wrapper">
        <header class="header">
            <a href="index.php">
                <img id="logoMenu" src="img/logoONG.png" alt="Logo APVAC">
            </a>
            <nav class="menu">
                <ul class="menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="sobre.php">Sobre</a></li>
                    <li><a href="projetos.php">Projetos</a></li>
                    <li><a href="doar.php">Doar</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
            <div class="acesso">
                <span>Olá, visitante! 👋</span>
                <a href="index.php"
                    style="background: white; color: #1a1a2e; padding: 8px 18px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                    Voltar ao Login
                </a>
            </div>
        </header>

        <main>
            <div class="recuperar-container">
                <div class="recuperar-box">
                    <h1><i class="fas fa-key"></i> Recuperar Senha</h1>
                    <p class="subtitulo">Digite seu e-mail e enviaremos um link para criar uma nova senha</p>

                    <?php if ($mensagem): ?>
                    <div class="<?php echo $tipo_mensagem == 'sucesso' ? 'resultado-sucesso' : 'resultado-erro'; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($tipo_mensagem != 'sucesso'): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>E-mail cadastrado *</label>
                            <input type="email" name="email" required placeholder="seu@email.com" autocomplete="off">
                        </div>

                        <button type="submit" class="btn-recuperar">
                            <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                        </button>
                    </form>
                    <?php endif; ?>

                    <div class="voltar-login">
                        <a href="index.php">
                            <i class="fas fa-arrow-left"></i> Voltar ao Login
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

</body>

</html>