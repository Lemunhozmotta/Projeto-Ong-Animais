<?php
session_start();
include "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "Faça login para acessar seu perfil!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

// Variáveis para o menu
$usuario_logado = true;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'visitante';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT u.*, d.nome as departamento_nome 
        FROM usuarios u 
        LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento 
        WHERE u.id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$sql = "SELECT a.*, an.nome as animal_nome, an.especie, an.porte 
        FROM adocoes a 
        JOIN animais an ON a.id_animal = an.id_animal 
        WHERE a.id_usuario = ? 
        ORDER BY a.data DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$adocoes = $resultado->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT * FROM doacoes WHERE id_usuario = ? ORDER BY data DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$doacoes = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Meu Perfil - APVAC</title>
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
                <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>! 👋</span>
                <span
                    style="font-size: 12px; background: rgba(255,255,255,0.2); padding: 2px 12px; border-radius: 12px;">
                    <?php echo getNomeNivel($_SESSION['usuario_nivel'] ?? 4); ?>
                </span>
                <a href="logout.php" class="btn-sair">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </header>

        <main>
            <div class="perfil-container">
                <a href="javascript:history.back()" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
                Voltar</a>

                <div class="perfil-header">
                    <h1><i class="fas fa-user-circle"></i> Meu Perfil</h1>
                    <div class="dados">
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
                        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                        <p><strong>Telefone:</strong>
                            <?php echo htmlspecialchars($usuario['telefone'] ?? 'Não informado'); ?></p>
                        <p><strong>Departamento:</strong>
                            <?php echo htmlspecialchars($usuario['departamento_nome'] ?? 'Não definido'); ?></p>
                    </div>
                </div>

                <div class="secao">
                    <h2><i class="fas fa-heart" style="color: #008b8b;"></i> Minhas Adoções</h2>
                    <?php if (count($adocoes) > 0): ?>
                    <?php foreach ($adocoes as $adocao): ?>
                    <div class="item-lista">
                        <div class="info">
                            <strong><?php echo htmlspecialchars($adocao['animal_nome']); ?></strong>
                            <span class="detalhe"> - <?php echo $adocao['especie']; ?>
                                (<?php echo $adocao['porte']; ?>)</span>
                            <br>
                            <span class="data"><i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($adocao['data'])); ?></span>
                        </div>
                        <?php if (isset($adocao['status'])): ?>
                        <span class="badge-status badge-<?php echo $adocao['status']; ?>">
                            <?php echo ucfirst($adocao['status']); ?>
                        </span>
                        <?php else: ?>
                        <span class="badge-status badge-pendente">Pendente</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="vazio">
                        <i class="fas fa-heart"></i>
                        <p>Você ainda não adotou nenhum animal.</p>
                        <a href="index.php#adocao">Conheça nossos animais</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="secao">
                    <h2><i class="fas fa-hand-holding-heart" style="color: #ff6b35;"></i> Minhas Doações</h2>
                    <?php if (count($doacoes) > 0): ?>
                    <?php foreach ($doacoes as $doacao): ?>
                    <div class="item-lista">
                        <div class="info">
                            <strong><?php echo ucfirst($doacao['tipo']); ?></strong>
                            <?php if ($doacao['tipo'] == 'dinheiro'): ?>
                            <span style="color: #28a745; font-weight: bold;">R$
                                <?php echo number_format($doacao['valor'], 2, ',', '.'); ?></span>
                            <?php else: ?>
                            <span><?php echo $doacao['quantidade']; ?> item(ns)</span>
                            <?php endif; ?>
                            <br>
                            <span class="data"><i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($doacao['data'])); ?></span>
                            <?php if ($doacao['descricao']): ?>
                            <br><span
                                style="font-size: 13px; color: #666;"><?php echo htmlspecialchars($doacao['descricao']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="vazio">
                        <i class="fas fa-hand-holding-heart"></i>
                        <p>Você ainda não fez nenhuma doação.</p>
                        <a href="doar.php">Faça uma doação</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

</body>

</html>