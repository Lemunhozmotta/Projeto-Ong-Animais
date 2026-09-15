<?php
session_start();
include "conexao.php";

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

$id = $_GET['id'] ?? 0;
$usuario_edit = getUsuarioById($conexao, $id);

if (!$usuario_edit) {
    $_SESSION['mensagem'] = "❌ Usuário não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: admin_usuarios.php');
    exit();
}

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {

    // EXCLUIR
    if ($_POST['acao'] == 'excluir') {
        if ($id == $_SESSION['usuario_id']) {
            $_SESSION['mensagem'] = "❌ Você não pode excluir a si mesmo!";
            $_SESSION['tipo_mensagem'] = "error";
        } else {
            $resultado = excluirUsuario($conexao, $id);
            if ($resultado['sucesso']) {
                $_SESSION['mensagem'] = "✅ Usuário excluído com sucesso! (" . $resultado['adocoes'] . " adoções e " . $resultado['doacoes'] . " doações removidas)";
                $_SESSION['tipo_mensagem'] = "success";
                header('Location: admin_usuarios.php');
                exit();
            } else {
                $_SESSION['mensagem'] = "❌ Erro ao excluir: " . $resultado['erro'];
                $_SESSION['tipo_mensagem'] = "error";
            }
        }
    }

    // ATUALIZAR
    if ($_POST['acao'] == 'atualizar') {
        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'] ?? '',
            'data_nascimento' => $_POST['data_nascimento'] ?? null,
            'endereco' => $_POST['endereco'] ?? '',
            'id_departamento' => $_POST['id_departamento'] ?? null,
            'nivel' => $_POST['nivel'] ?? 4,
            'nova_senha' => $_POST['nova_senha'] ?? ''
        ];

        // Verificar se o email já existe em outro usuário
        $check_sql = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
        $check_stmt = $conexao->prepare($check_sql);
        $check_stmt->bind_param("si", $dados['email'], $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $_SESSION['mensagem'] = "❌ Este e-mail já está sendo usado por outro usuário!";
            $_SESSION['tipo_mensagem'] = "error";
        } else {
            if (atualizarUsuario($conexao, $id, $dados)) {
                $_SESSION['mensagem'] = "✅ Usuário atualizado com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
                header('Location: admin_usuarios.php');
                exit();
            } else {
                $_SESSION['mensagem'] = "❌ Erro ao atualizar usuário.";
                $_SESSION['tipo_mensagem'] = "error";
            }
        }
    }
}

// Recarregar dados
$usuario_edit = getUsuarioById($conexao, $id);
$departamentos = buscarDepartamentos($conexao);
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
$total_pendentes = contarAdocoesPendentes($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logoONG_icon.png">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Editar Usuário - APVAC</title>
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
            <div class="admin-container">

                <a href="admin_usuarios.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>

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

                <div class="form-animal ativo">
                    <h2 style="margin-bottom: 20px;">
                        <i class="fas fa-user-edit" style="color: #008b8b;"></i>
                        Editar Usuário: <?php echo htmlspecialchars($usuario_edit['nome']); ?>
                    </h2>

                    <form method="POST">
                        <input type="hidden" name="acao" value="atualizar">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nome Completo *</label>
                                <input type="text" name="nome"
                                    value="<?php echo htmlspecialchars($usuario_edit['nome']); ?>" required
                                    maxlength="30">
                            </div>
                            <div class="form-group">
                                <label>E-mail *</label>
                                <input type="email" name="email"
                                    value="<?php echo htmlspecialchars($usuario_edit['email']); ?>" required
                                    maxlength="50">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="text" name="telefone"
                                    value="<?php echo htmlspecialchars($usuario_edit['telefone'] ?? ''); ?>"
                                    maxlength="20">
                            </div>
                            <div class="form-group">
                                <label>Data de Nascimento</label>
                                <input type="date" name="data_nascimento"
                                    value="<?php echo $usuario_edit['data_nascimento']; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Endereço</label>
                                <input type="text" name="endereco"
                                    value="<?php echo htmlspecialchars($usuario_edit['endereco'] ?? ''); ?>"
                                    maxlength="50">
                            </div>
                            <div class="form-group">
                                <label>Departamento</label>
                                <select name="id_departamento">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($departamentos as $dept): ?>
                                    <option value="<?php echo $dept['id_departamento']; ?>"
                                        <?php echo ($usuario_edit['id_departamento'] == $dept['id_departamento']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['nome']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nível de Acesso</label>
                                <select name="nivel" <?php echo ($id == $_SESSION['usuario_id']) ? 'disabled' : ''; ?>>
                                    <option value="1" <?php echo ($usuario_edit['nivel'] == 1) ? 'selected' : ''; ?>>1 -
                                        Administrador</option>
                                    <option value="2" <?php echo ($usuario_edit['nivel'] == 2) ? 'selected' : ''; ?>>2 -
                                        Gestor</option>
                                    <option value="3" <?php echo ($usuario_edit['nivel'] == 3) ? 'selected' : ''; ?>>3 -
                                        Voluntário</option>
                                    <option value="4" <?php echo ($usuario_edit['nivel'] == 4) ? 'selected' : ''; ?>>4 -
                                        Usuário</option>
                                </select>
                                <?php if ($id == $_SESSION['usuario_id']): ?>
                                <input type="hidden" name="nivel" value="<?php echo $usuario_edit['nivel']; ?>">
                                <small style="color: #999;">Você não pode alterar seu próprio nível</small>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Nova Senha (deixe em branco para manter)</label>
                                <input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres" minlength="6"
                                    autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="btn-salvar">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>

                    <?php if ($id != $_SESSION['usuario_id']): ?>
                    <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

                    <h3 style="color: #dc3545; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i> Zona de Perigo
                    </h3>
                    <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                        Excluir este usuário removerá também todas as suas adoções e doações. Esta ação é
                        <strong>permanente</strong>.
                    </p>

                    <form method="POST"
                        onsubmit="return confirm('⚠️ ATENÇÃO!\n\nTem certeza que deseja excluir o usuário <?php echo htmlspecialchars($usuario_edit['nome']); ?>?\n\nEsta ação irá remover:\n- O usuário\n- Todas as adoções dele\n- Todas as doações dele\n\nNÃO PODE SER DESFEITO!');">
                        <input type="hidden" name="acao" value="excluir">
                        <button type="submit"
                            style="width: 100%; padding: 14px; background: #dc3545; color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 16px;">
                            <i class="fas fa-trash"></i> Excluir Usuário Permanentemente
                        </button>
                    </form>
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