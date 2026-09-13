<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "conexao.php";
require_once "mailer.php";


$total_pendentes = 0;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] <= 2) {
    $total_pendentes = contarAdocoesPendentes($conexao);
}

$usuario_logado = isset($_SESSION['usuario_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'visitante';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
$total_pendentes = 0;
if ($usuario_logado && $nivel_usuario <= 2) {
    $total_pendentes = contarAdocoesPendentes($conexao);
}

$mensagem = '';
$tipo_mensagem = '';

if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'] ?? 'success';
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}

// Processar formulário de contato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assunto = trim($_POST['assunto'] ?? 'Sem assunto');
    $mensagem_texto = trim($_POST['mensagem'] ?? '');

    if (empty($nome) || empty($email) || empty($mensagem_texto)) {
        $_SESSION['mensagem'] = "❌ Preencha todos os campos obrigatórios!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: contato.php');
        exit();
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensagem'] = "❌ E-mail inválido!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: contato.php');
        exit();
    }

    // E-mail para a ONG
    $email_ong = "apvac.projeto@protonmail.com";

    // Corpo do e-mail
    $corpo = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f0f2f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; }
            .header { background: #1a1a2e; color: white; padding: 25px; text-align: center; }
            .header h1 { margin: 0; font-size: 22px; }
            .content { padding: 30px; }
            .info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #008b8b; }
            .info p { margin: 8px 0; color: #333; }
            .info strong { color: #1a1a2e; }
            .mensagem { background: #f0f7f7; padding: 20px; border-radius: 8px; color: #333; line-height: 1.6; }
            .footer { background: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🐾 Nova Mensagem - APVAC</h1>
            </div>
            <div class='content'>
                <div class='info'>
                    <p><strong>Nome:</strong> " . htmlspecialchars($nome) . "</p>
                    <p><strong>E-mail:</strong> " . htmlspecialchars($email) . "</p>
                    <p><strong>Assunto:</strong> " . htmlspecialchars($assunto) . "</p>
                    <p><strong>Data:</strong> " . date('d/m/Y H:i') . "</p>
                </div>
                <div class='mensagem'>
                    <strong>Mensagem:</strong><br><br>
                    " . nl2br(htmlspecialchars($mensagem_texto)) . "
                </div>
            </div>
            <div class='footer'>
                <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Enviar e-mail
    if (enviarEmail($email_ong, "Contato do Site: " . $assunto, $corpo)) {
        $_SESSION['mensagem'] = "✅ Mensagem enviada com sucesso! Entraremos em contato em breve.";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "❌ Erro ao enviar mensagem. Tente novamente mais tarde.";
        $_SESSION['tipo_mensagem'] = "error";
    }

    header('Location: contato.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_contato.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Contato - APVAC</title>
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
                    <li><a href="contato.php" class="ativo">Contato</a></li>
                    <li><a href="perfil.php">Meu Perfil</a></li>
                    <?php if ($usuario_logado && $nivel_usuario <= 2): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-cog"></i> Admin
                            <?php if ($total_pendentes > 0): ?>
                            <span class="badge-notif-menu"><?php echo $total_pendentes; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="admin_animais.php"><i class="fas fa-paw"></i> Gerenciar Animais</a></li>
                            <li><a href="admin_adocoes.php"><i class="fas fa-heart"></i> Gerenciar Adoções</a></li>
                            <li><a href="admin_doacoes.php"><i class="fas fa-hand-holding-heart"></i> Gerenciar
                                    Doações</a></li>
                            <?php if ($nivel_usuario == 1): ?>
                            <li><a href="admin_usuarios.php"><i class="fas fa-users"></i> Gerenciar Usuários</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="acesso">
                <?php if ($usuario_logado): ?>
                <span>Olá, <?php echo htmlspecialchars($nome_usuario); ?>! 👋</span>
                <span
                    style="font-size: 12px; background: rgba(255,255,255,0.2); padding: 2px 12px; border-radius: 12px;">
                    <?php echo $nome_nivel; ?>
                </span>
                <a href="logout.php" class="btn-sair">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
                <?php else: ?>
                <span>Olá, visitante! 👋</span>
                <button type="button" id="abrirLogin">
                    Faça seu login aqui
                </button>
                <?php endif; ?>
            </div>
        </header>

        <main>
            <div class="contato-container">
                <a href="javascript:history.back()" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>

                <div class="contato-header">
                    <h1><i class="fas fa-envelope"></i> Entre em Contato</h1>
                    <p>Tem dúvidas, sugestões ou quer ajudar? Fale com a gente!</p>
                </div>

                <?php if ($mensagem): ?>
                <div
                    style="max-width: 100%; margin-bottom: 20px; padding: 15px; border-radius: 10px; text-align: center; 
                        <?php echo $tipo_mensagem == 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                    <?php echo $mensagem; ?>
                </div>
                <?php endif; ?>

                <div class="contato-grid">
                    <div class="contato-info-box">
                        <h2><i class="fas fa-info-circle"></i> Informações</h2>

                        <div class="contato-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Endereço</strong>
                                <span>Cubatão - SP<br>Brasil</span>
                            </div>
                        </div>

                        <div class="contato-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>E-mail</strong>
                                <span>contato@apvac.org.br</span>
                            </div>
                        </div>

                        <div class="contato-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Telefone</strong>
                                <span>(13) 99999-9999</span>
                            </div>
                        </div>

                        <div class="contato-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Horário</strong>
                                <span>Segunda a Sexta: 9h às 18h<br>Sábado: 9h às 13h</span>
                            </div>
                        </div>

                        <div class="contato-item">
                            <i class="fas fa-share-alt"></i>
                            <div>
                                <strong>Redes Sociais</strong>
                                <span>@apvac_oficial</span>
                            </div>
                        </div>
                    </div>

                    <div class="contato-form-box">
                        <h2><i class="fas fa-paper-plane"></i> Envie uma Mensagem</h2>

                        <form method="POST">
                            <div class="form-group">
                                <label>Nome *</label>
                                <input type="text" name="nome" required
                                    value="<?php echo $usuario_logado ? htmlspecialchars($nome_usuario) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>E-mail *</label>
                                <input type="email" name="email" required
                                    value="<?php echo $usuario_logado ? htmlspecialchars($_SESSION['usuario_email'] ?? '') : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>Assunto</label>
                                <input type="text" name="assunto" placeholder="Ex: Quero ser voluntário">
                            </div>

                            <div class="form-group">
                                <label>Mensagem *</label>
                                <textarea name="mensagem" required placeholder="Escreva sua mensagem..."></textarea>
                            </div>

                            <button type="submit" class="btn-enviar">
                                <i class="fas fa-paper-plane"></i> Enviar Mensagem
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

    <!-- MODAL LOGIN/CADASTRO -->
    <div id="modalAcesso"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">

        <div
            style="background: white; padding: 30px; border-radius: 10px; max-width: 400px; width: 90%; position: relative; max-height: 90vh; overflow-y: auto;">

            <button onclick="fecharModal()"
                style="position: absolute; right: 15px; top: 10px; font-size: 30px; border: none; background: none; cursor: pointer;">
                &times;
            </button>

            <div id="areaLogin">
                <h2 style="text-align: center; margin-bottom: 10px;">Bem-vindo!</h2>
                <p style="text-align: center; margin-bottom: 20px;">Entre para acessar sua conta.</p>

                <form action="login.php" method="POST" autocomplete="off">
                    <label style="display: block; margin-top: 10px; font-weight: bold;">E-mail</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">Senha</label>
                    <input type="password" name="senha" required
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <button type="submit"
                        style="width: 100%; padding: 12px; background: black; color: white; border: none; border-radius: 5px; margin-top: 15px; cursor: pointer; font-weight: bold;">
                        Entrar
                    </button>
                </form>

                <p style="text-align: center; margin-top: 12px;">
                    <a href="recuperar_senha.php"
                        style="color: #008b8b; font-weight: 600; font-size: 14px; text-decoration: none;">
                        <i class="fas fa-key"></i> Esqueci minha senha
                    </a>
                </p>

                <p style="text-align: center; margin-top: 15px;">
                    Ainda não possui cadastro?
                    <a href="#" onclick="mostrarCadastro()" style="color: #008b8b; font-weight: bold; cursor: pointer;">
                        Cadastre-se aqui
                    </a>
                </p>
            </div>

            <div id="areaCadastro" style="display: none;">
                <h2 style="text-align: center; margin-bottom: 10px;">Crie sua conta</h2>
                <p style="text-align: center; margin-bottom: 20px;">Cadastre-se para participar da APVAC.</p>

                <form action="cadastro.php" method="POST" autocomplete="off">
                    <label style="display: block; margin-top: 10px; font-weight: bold;">Nome completo</label>
                    <input type="text" name="nome" required
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">E-mail</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">Senha</label>
                    <input type="password" name="senha" required
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">Telefone</label>
                    <input type="text" name="telefone"
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">Data de nascimento</label>
                    <input type="date" name="data_nascimento"
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">Endereço</label>
                    <input type="text" name="endereco"
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">

                    <label style="display: block; margin-top: 10px; font-weight: bold;">Departamento</label>
                    <select name="id_departamento"
                        style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">
                        <option value="">Selecione...</option>
                        <?php
                        $departamentos = buscarDepartamentos($conexao);
                        foreach ($departamentos as $dept): ?>
                        <option value="<?php echo $dept['id_departamento']; ?>">
                            <?php echo htmlspecialchars($dept['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit"
                        style="width: 100%; padding: 12px; background: black; color: white; border: none; border-radius: 5px; margin-top: 15px; cursor: pointer; font-weight: bold;">
                        Cadastrar
                    </button>
                </form>

                <p style="text-align: center; margin-top: 15px;">
                    Já possui cadastro?
                    <a href="#" onclick="mostrarLogin()" style="color: #008b8b; font-weight: bold; cursor: pointer;">
                        Voltar para o login
                    </a>
                </p>
            </div>

        </div>
    </div>

    <script>
    function abrirModal() {
        document.getElementById('modalAcesso').style.display = 'flex';
        document.getElementById('areaLogin').style.display = 'block';
        document.getElementById('areaCadastro').style.display = 'none';
    }

    function fecharModal() {
        document.getElementById('modalAcesso').style.display = 'none';
    }

    function mostrarCadastro() {
        document.getElementById('areaLogin').style.display = 'none';
        document.getElementById('areaCadastro').style.display = 'block';
    }

    function mostrarLogin() {
        document.getElementById('areaCadastro').style.display = 'none';
        document.getElementById('areaLogin').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var btnLogin = document.getElementById('abrirLogin');
        if (btnLogin) {
            btnLogin.addEventListener('click', abrirModal);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fecharModal();
            }
        });
    });
    </script>

</body>

</html>