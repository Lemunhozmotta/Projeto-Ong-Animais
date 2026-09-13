<?php
session_start();
include "conexao.php";

$total_pendentes = 0;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] <= 2) {
    $total_pendentes = contarAdocoesPendentes($conexao);
}

$usuario_logado = isset($_SESSION['usuario_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'visitante';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_sobre.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Sobre Nós - APVAC</title>
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
                    <li><a href="perfil.php">Meu Perfil</a></li>
                    <?php if ($usuario_logado && $nivel_usuario <= 2): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-cog"></i> Admin
                            <?php if (isset($total_pendentes) && $total_pendentes > 0): ?>
                            <span class="badge-notif-menu"><?php echo $total_pendentes; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="admin_animais.php">
                                    <i class="fas fa-paw"></i> Gerenciar Animais
                                </a>
                            </li>
                            <li>
                                <a href="admin_adocoes.php">
                                    <i class="fas fa-heart"></i> Gerenciar Adoções
                                </a>
                            </li>
                            <li><a href="admin_doacoes.php"><i class="fas fa-hand-holding-heart"></i> Gerenciar
                                    Doações</a></li>
                            <?php if ($nivel_usuario == 1): ?>
                            <li>
                                <a href="admin_usuarios.php">
                                    <i class="fas fa-users"></i> Gerenciar Usuários
                                </a>
                            </li>
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
            <div class="sobre-container">
                <a href="javascript:history.back()" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>

                <div class="sobre-header">
                    <h1><i class="fas fa-paw"></i> Sobre a APVAC</h1>
                    <p>Associação Proteção à Vida Animal Cubatão - Dedicados ao bem-estar e proteção dos animais desde
                        nossa fundação.</p>
                </div>

                <div class="sobre-secao">
                    <h2><i class="fas fa-heart"></i> Quem Somos</h2>
                    <p>
                        A APVAC (Associação Proteção à Vida Animal Cubatão) é uma organização sem fins lucrativos
                        dedicada ao resgate, cuidado, recuperação e adoção responsável de animais em situação de
                        vulnerabilidade na região de Cubatão e arredores.
                    </p>
                    <p>
                        Nosso trabalho é realizado por voluntários apaixonados pela causa animal, que dedicam seu
                        tempo e amor para garantir que cada animal resgatado receba os cuidados necessários e
                        encontre um lar amoroso.
                    </p>
                </div>

                <div class="sobre-secao">
                    <h2><i class="fas fa-bullseye"></i> Nossa Missão</h2>
                    <p>
                        Promover a proteção, o bem-estar e a dignidade dos animais, através do resgate, tratamento,
                        castração, adoção responsável e conscientização da comunidade sobre a importância do respeito
                        aos animais.
                    </p>
                </div>

                <div class="sobre-secao">
                    <h2><i class="fas fa-eye"></i> Nossa Visão</h2>
                    <p>
                        Ser referência na proteção animal na região, construindo uma sociedade mais consciente e
                        compassiva, onde nenhum animal sofra maus-tratos ou abandono.
                    </p>
                </div>

                <div class="sobre-secao">
                    <h2><i class="fas fa-star"></i> Nossos Valores</h2>
                    <div class="valores-grid">
                        <div class="valor-card">
                            <i class="fas fa-heart"></i>
                            <h3>Amor</h3>
                            <p>Cuidamos de cada animal com todo carinho e dedicação.</p>
                        </div>
                        <div class="valor-card">
                            <i class="fas fa-handshake"></i>
                            <h3>Respeito</h3>
                            <p>Tratamos todos os seres vivos com dignidade.</p>
                        </div>
                        <div class="valor-card">
                            <i class="fas fa-users"></i>
                            <h3>Comunidade</h3>
                            <p>Acreditamos na força da união para transformar vidas.</p>
                        </div>
                        <div class="valor-card">
                            <i class="fas fa-leaf"></i>
                            <h3>Responsabilidade</h3>
                            <p>Compromisso com o bem-estar animal e ambiental.</p>
                        </div>
                    </div>
                </div>

                <div class="sobre-secao">
                    <h2><i class="fas fa-tasks"></i> O Que Fazemos</h2>
                    <ul>
                        <li>Resgate de animais em situação de risco e abandono</li>
                        <li>Atendimento veterinário e tratamentos necessários</li>
                        <li>Castração e controle populacional</li>
                        <li>Adoção responsável com acompanhamento</li>
                        <li>Programas de conscientização e educação animal</li>
                        <li>Eventos de arrecadação e feiras de adoção</li>
                        <li>Parcerias com protetores independentes</li>
                    </ul>
                </div>

                <div class="sobre-secao">
                    <h2><i class="fas fa-phone"></i> Entre em Contato</h2>
                    <div class="contato-info">
                        <div class="contato-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>E-mail</strong>
                                <span>apvac.projeto@protonmail.com</span>
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
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Localização</strong>
                                <span>Cubatão - SP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

    <?php include 'modal_login.php'; ?>

</body>

</html>