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

// Excluir animal
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    if (excluirAnimal($conexao, $id_excluir)) {
        $_SESSION['mensagem'] = "✅ Animal excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "❌ Erro ao excluir animal.";
        $_SESSION['tipo_mensagem'] = "error";
    }
    header('Location: admin_animais.php');
    exit();
}

// Cadastrar novo animal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $dados = [
        'nome' => $_POST['nome'],
        'especie' => $_POST['especie'],
        'porte' => $_POST['porte'],
        'data_acolhimento' => $_POST['data_acolhimento'],
        'idade_aparente' => $_POST['idade_aparente'],
        'saude' => $_POST['saude']
    ];

    $foto = $_FILES['foto'] ?? null;

    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $id = adicionarAnimal($conexao, $dados, $foto);
        if ($id) {
            $_SESSION['mensagem'] = "✅ Animal cadastrado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
            header('Location: admin_animais.php');
            exit();
        } else {
            $_SESSION['mensagem'] = "❌ Erro ao cadastrar animal.";
            $_SESSION['tipo_mensagem'] = "error";
        }
    } else {
        $_SESSION['mensagem'] = "❌ Selecione uma foto para o animal.";
        $_SESSION['tipo_mensagem'] = "error";
    }
}

$animais = buscarAnimais($conexao);
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
    <title>Gerenciar Animais - APVAC</title>
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

                <div class="admin-header">
                    <h1><i class="fas fa-paw"></i> Gerenciar Animais</h1>
                    <button class="btn-cadastrar" onclick="toggleForm()">
                        <i class="fas fa-plus"></i> Novo Animal
                    </button>
                </div>

                <div class="form-animal" id="formAnimal">
                    <h2 style="margin-bottom: 20px;"><i class="fas fa-plus-circle" style="color: #008b8b;"></i>
                        Cadastrar Novo Animal</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nome do Animal *</label>
                                <input type="text" name="nome" required>
                            </div>
                            <div class="form-group">
                                <label>Espécie *</label>
                                <select name="especie" required>
                                    <option value="Cão">Cão</option>
                                    <option value="Gato">Gato</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Porte *</label>
                                <select name="porte" required>
                                    <option value="pequeno">Pequeno</option>
                                    <option value="médio">Médio</option>
                                    <option value="grande">Grande</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Data de Acolhimento *</label>
                                <input type="date" name="data_acolhimento" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Idade Aparente *</label>
                                <select name="idade_aparente" required>
                                    <option value="filhote">Filhote</option>
                                    <option value="adulto">Adulto</option>
                                    <option value="idoso">Idoso</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Foto do Animal</label>
                                <input type="file" name="foto" accept="image/*">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Estado de Saúde *</label>
                            <textarea name="saude" required placeholder="Descreva a saúde do animal..."></textarea>
                        </div>

                        <button type="submit" name="cadastrar" class="btn-salvar">
                            <i class="fas fa-save"></i> Cadastrar Animal
                        </button>
                    </form>
                </div>

                <h2 style="margin: 30px 0 15px 0; color: #1a1a2e;">
                    <i class="fas fa-list" style="color: #008b8b;"></i> Animais Cadastrados
                </h2>

                <div class="grid-animais">
                    <?php if (count($animais) > 0): ?>
                    <?php foreach ($animais as $animal): ?>
                    <div class="card-animal-admin">
                        <?php if (!empty($animal['foto']) && file_exists($animal['foto'])): ?>
                        <img src="<?php echo $animal['foto']; ?>" alt="<?php echo $animal['nome']; ?>">
                        <?php else: ?>
                        <div class="sem-imagem">
                            <i class="fas fa-paw"></i>
                        </div>
                        <?php endif; ?>
                        <div class="info">
                            <h4><?php echo htmlspecialchars($animal['nome']); ?></h4>
                            <p><?php echo $animal['especie']; ?> - <?php echo $animal['porte']; ?></p>
                            <p style="font-size: 13px; color: #999;">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($animal['data_acolhimento'])); ?>
                            </p>
                            <span class="badge-disponivel">Disponível</span>

                            <div style="display: flex; gap: 8px; margin-top: 12px;">
                                <a href="admin_editar_animal.php?id=<?php echo $animal['id_animal']; ?>"
                                    style="flex: 1; background: #008b8b; color: white; text-align: center; padding: 8px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="admin_animais.php?excluir=<?php echo $animal['id_animal']; ?>"
                                    onclick="return confirm('Tem certeza que deseja excluir <?php echo htmlspecialchars($animal['nome']); ?>?');"
                                    style="flex: 1; background: #dc3545; color: white; text-align: center; padding: 8px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
                        Nenhum animal cadastrado ainda.
                    </p>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

    <script>
    function toggleForm() {
        var form = document.getElementById('formAnimal');
        form.classList.toggle('ativo');
        if (form.classList.contains('ativo')) {
            form.scrollIntoView({
                behavior: 'smooth'
            });
        }
    }
    </script>

</body>

</html>