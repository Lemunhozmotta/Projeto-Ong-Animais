<?php
session_start();
include "conexao.php";

$total_pendentes = 0;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] <= 2) {
    $total_pendentes = contarAdocoesPendentes($conexao);
}

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "Faça login para acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

// Variáveis para o menu
$usuario_logado = true;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'visitante';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);

if (!podeAcessarAdmin($conexao, $_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "❌ Você não tem permissão para acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$animal = getAnimalById($conexao, $id);

if (!$animal) {
    $_SESSION['mensagem'] = "❌ Animal não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: admin_animais.php');
    exit();
}

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => $_POST['nome'],
        'especie' => $_POST['especie'],
        'porte' => $_POST['porte'],
        'data_acolhimento' => $_POST['data_acolhimento'],
        'idade_aparente' => $_POST['idade_aparente'],
        'saude' => $_POST['saude']
    ];

    $foto = $_FILES['foto'] ?? null;

    if (atualizarAnimal($conexao, $id, $dados, $foto)) {
        $_SESSION['mensagem'] = "✅ Animal atualizado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header('Location: admin_animais.php');
        exit();
    } else {
        $_SESSION['mensagem'] = "❌ Erro ao atualizar animal.";
        $_SESSION['tipo_mensagem'] = "error";
    }
}

$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Editar Animal - APVAC</title>
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
                    <?php echo $nome_nivel; ?>
                </span>
                <a href="logout.php" class="btn-sair">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </header>

        <main>
            <div class="admin-container">

                <a href="admin_animais.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>

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
                    <h2 style="margin-bottom: 20px;"><i class="fas fa-edit" style="color: #008b8b;"></i> Editar:
                        <?php echo htmlspecialchars($animal['nome']); ?></h2>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nome do Animal *</label>
                                <input type="text" name="nome" value="<?php echo htmlspecialchars($animal['nome']); ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Espécie *</label>
                                <select name="especie" required>
                                    <option value="Cão" <?php echo $animal['especie'] == 'Cão' ? 'selected' : ''; ?>>Cão
                                    </option>
                                    <option value="Gato" <?php echo $animal['especie'] == 'Gato' ? 'selected' : ''; ?>>
                                        Gato</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Porte *</label>
                                <select name="porte" required>
                                    <option value="pequeno"
                                        <?php echo $animal['porte'] == 'pequeno' ? 'selected' : ''; ?>>Pequeno</option>
                                    <option value="médio" <?php echo $animal['porte'] == 'médio' ? 'selected' : ''; ?>>
                                        Médio</option>
                                    <option value="grande"
                                        <?php echo $animal['porte'] == 'grande' ? 'selected' : ''; ?>>Grande</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Data de Acolhimento *</label>
                                <input type="date" name="data_acolhimento"
                                    value="<?php echo $animal['data_acolhimento']; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Idade Aparente *</label>
                                <select name="idade_aparente" required>
                                    <option value="filhote"
                                        <?php echo $animal['idade_aparente'] == 'filhote' ? 'selected' : ''; ?>>Filhote
                                    </option>
                                    <option value="adulto"
                                        <?php echo $animal['idade_aparente'] == 'adulto' ? 'selected' : ''; ?>>Adulto
                                    </option>
                                    <option value="idoso"
                                        <?php echo $animal['idade_aparente'] == 'idoso' ? 'selected' : ''; ?>>Idoso
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nova Foto (opcional)</label>
                                <input type="file" name="foto" accept="image/*">
                            </div>
                        </div>

                        <?php if (!empty($animal['foto']) && file_exists($animal['foto'])): ?>
                        <div class="form-group">
                            <label>Foto Atual:</label>
                            <img src="<?php echo $animal['foto']; ?>" alt="Foto atual"
                                style="max-width: 200px; border-radius: 10px;">
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Estado de Saúde *</label>
                            <textarea name="saude" required><?php echo htmlspecialchars($animal['saude']); ?></textarea>
                        </div>

                        <button type="submit" class="btn-salvar">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>

            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

</body>

</html>