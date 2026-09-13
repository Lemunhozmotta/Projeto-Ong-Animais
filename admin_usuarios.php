<?php
session_start();
include "conexao.php";

$total_pendentes = 0;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] <= 2) {
    $total_pendentes = contarAdocoesPendentes($conexao);
}

// Variáveis para o menu
$usuario_logado = true;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'visitante';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);


if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "Faça login para acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

if ($_SESSION['usuario_nivel'] != 1) {
    $_SESSION['mensagem'] = "❌ Apenas administradores podem acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

// Buscar todos os usuários
$sql = "SELECT u.*, d.nome as departamento_nome 
        FROM usuarios u 
        LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento 
        ORDER BY u.id_usuario ASC";
$resultado = $conexao->query($sql);
$usuarios = $resultado->fetch_all(MYSQLI_ASSOC);

$niveis = [
    1 => 'Administrador',
    2 => 'Gestor',
    3 => 'Voluntário',
    4 => 'Usuário'
];

$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
$total_pendentes = contarAdocoesPendentes($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_admin_usuarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Gerenciar Usuários - APVAC</title>
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
                    <?php if ($nivel_usuario <= 2): ?>
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
                <span>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>! 👋</span>
                <span
                    style="font-size: 12px; background: rgba(255,255,255,0.2); padding: 2px 12px; border-radius: 12px;">
                    <?php echo $nome_nivel; ?>
                </span>
                <a href="logout.php" class="btn-sair">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </header>

        <main>
            <div class="admin-usuarios-container">

                <?php if (isset($_SESSION['mensagem'])): ?>
                <div
                    style="padding: 15px; background: <?php echo $_SESSION['tipo_mensagem'] == 'success' ? '#d4edda' : '#f8d7da'; ?>; border-radius: 10px; margin-bottom: 20px; color: <?php echo $_SESSION['tipo_mensagem'] == 'success' ? '#155724' : '#721c24'; ?>;">
                    <?php
                        echo $_SESSION['mensagem'];
                        unset($_SESSION['mensagem']);
                        unset($_SESSION['tipo_mensagem']);
                        ?>
                </div>
                <?php endif; ?>

                <div class="admin-usuarios-header">
                    <h1><i class="fas fa-users"></i> Gerenciar Usuários</h1>
                    <span style="color: #666; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Total: <?php echo count($usuarios); ?> usuários
                    </span>
                </div>

                <div class="tabela-usuarios">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Departamento</th>
                                <th>Nível</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id_usuario']; ?></td>
                                <td><strong><?php echo htmlspecialchars($usuario['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['departamento_nome'] ?? 'Não definido'); ?>
                                </td>
                                <td>
                                    <span class="badge-nivel badge-nivel-<?php echo $usuario['nivel'] ?? 4; ?>">
                                        <?php echo $usuario['nivel'] ?? 4; ?> -
                                        <?php echo $niveis[$usuario['nivel'] ?? 4]; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($usuario['id_usuario'] != $_SESSION['usuario_id']): ?>
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <a href="admin_editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>"
                                            style="background: #008b8b; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="admin_editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>"
                                            style="background: #dc3545; color: white; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                            <i class="fas fa-trash"></i> Excluir
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <span style="color: #999; font-size: 13px;">Você</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="vazio-tabela">
                                    <i class="fas fa-users"
                                        style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                    Nenhum usuário cadastrado ainda.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

</body>

</html>