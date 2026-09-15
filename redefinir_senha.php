<?php
session_start();
include "conexao.php";

$mensagem = '';
$tipo_mensagem = '';
$token_valido = false;
$usuario = null;

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT * FROM usuarios WHERE token_recuperacao = ? AND expira_token > NOW()";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario) {
        $token_valido = true;
    } else {
        $mensagem = "❌ Link inválido ou expirado. Solicite um novo link de recuperação.";
        $tipo_mensagem = "erro";
    }
}

// Processar nova senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $token = $_POST['token'] ?? '';

    if (strlen($nova_senha) < 6) {
        $mensagem = "❌ A senha deve ter pelo menos 6 caracteres!";
        $tipo_mensagem = "erro";
    } elseif ($nova_senha !== $confirmar_senha) {
        $mensagem = "❌ As senhas não coincidem!";
        $tipo_mensagem = "erro";
    } else {
        // Criptografar nova senha
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Atualizar senha e limpar token
        $update_sql = "UPDATE usuarios SET senha = ?, token_recuperacao = NULL, expira_token = NULL WHERE token_recuperacao = ?";
        $update_stmt = $conexao->prepare($update_sql);
        $update_stmt->bind_param("ss", $senha_hash, $token);

        if ($update_stmt->execute()) {
            $_SESSION['mensagem'] = "✅ Senha redefinida com sucesso! Faça login com sua nova senha.";
            $_SESSION['tipo_mensagem'] = "success";
            header('Location: index.php');
            exit();
        } else {
            $mensagem = "❌ Erro ao redefinir senha. Tente novamente.";
            $tipo_mensagem = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logoONG_icon.png">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_recuperar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Redefinir Senha - APVAC</title>
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
                    <h1><i class="fas fa-lock"></i> Redefinir Senha</h1>

                    <?php if ($token_valido): ?>
                    <p class="subtitulo">Olá, <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong>! Crie
                        sua nova senha abaixo.</p>

                    <?php if ($mensagem): ?>
                    <div class="<?php echo $tipo_mensagem == 'sucesso' ? 'resultado-sucesso' : 'resultado-erro'; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

                        <div class="form-group">
                            <label>Nova Senha * (mínimo 6 caracteres)</label>
                            <input type="password" name="nova_senha" required minlength="6"
                                placeholder="Digite a nova senha">
                        </div>

                        <div class="form-group">
                            <label>Confirmar Nova Senha *</label>
                            <input type="password" name="confirmar_senha" required minlength="6"
                                placeholder="Repita a nova senha">
                        </div>

                        <button type="submit" class="btn-recuperar">
                            <i class="fas fa-check"></i> Salvar Nova Senha
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="resultado-erro">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $mensagem; ?>
                    </div>

                    <a href="recuperar_senha.php" class="btn-recuperar"
                        style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">
                        <i class="fas fa-redo"></i> Solicitar Novo Link
                    </a>
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