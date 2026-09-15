<?php
session_start();
include "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "Faça login para acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

if (!podeAcessarAdmin($conexao, $_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "❌ Você não tem permissão para acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $id_doacao = $_POST['id_doacao'];

    if ($_POST['acao'] == 'confirmar') {
        if (confirmarDoacao($conexao, $id_doacao, $_SESSION['usuario_id'])) {
            $_SESSION['mensagem'] = "✅ Doação confirmada com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "❌ Erro ao confirmar doação.";
            $_SESSION['tipo_mensagem'] = "error";
        }
    }

    if ($_POST['acao'] == 'excluir') {
        if (excluirDoacao($conexao, $id_doacao)) {
            $_SESSION['mensagem'] = "✅ Doação excluída!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "❌ Erro ao excluir doação.";
            $_SESSION['tipo_mensagem'] = "error";
        }
    }

    header('Location: admin_doacoes.php');
    exit();
}

// Filtro
$filtro = $_GET['filtro'] ?? 'todas';

$sql = "SELECT d.*, u.nome as usuario_nome, u.email as usuario_email, u.telefone as usuario_telefone
        FROM doacoes d
        LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario";

if ($filtro == 'pendentes') {
    $sql .= " WHERE d.status = 'pendente'";
} elseif ($filtro == 'confirmadas') {
    $sql .= " WHERE d.status = 'confirmado'";
} elseif ($filtro == 'dinheiro') {
    $sql .= " WHERE d.tipo = 'dinheiro'";
} elseif ($filtro == 'itens') {
    $sql .= " WHERE d.tipo = 'item'";
}

$sql .= " ORDER BY d.status = 'pendente' DESC, d.data DESC";

$resultado = $conexao->query($sql);
$doacoes = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

$total_pendentes = contarDoacoesPendentes($conexao);
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
$total_pendentes_adocoes = contarAdocoesPendentes($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logoONG_icon.png">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_admin_doacoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Gerenciar Doações - APVAC</title>
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
                            <?php if ($total_pendentes_adocoes > 0): ?>
                            <span class="badge-notif-menu"><?php echo $total_pendentes_adocoes; ?></span>
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
            <div class="admin-doacoes-container">

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

                <div class="admin-doacoes-header">
                    <h1><i class="fas fa-hand-holding-heart"></i> Gerenciar Doações</h1>
                    <?php if ($total_pendentes > 0): ?>
                    <span class="badge-pendente-count">
                        <i class="fas fa-clock"></i> <?php echo $total_pendentes; ?> pendente(s)
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Filtros -->
                <div class="filtros-doacoes">
                    <a href="admin_doacoes.php?filtro=todas"
                        class="filtro-btn <?php echo $filtro == 'todas' ? 'ativo' : ''; ?>">
                        <i class="fas fa-list"></i> Todas
                    </a>
                    <a href="admin_doacoes.php?filtro=pendentes"
                        class="filtro-btn <?php echo $filtro == 'pendentes' ? 'ativo' : ''; ?>">
                        <i class="fas fa-clock"></i> Pendentes
                    </a>
                    <a href="admin_doacoes.php?filtro=confirmadas"
                        class="filtro-btn <?php echo $filtro == 'confirmadas' ? 'ativo' : ''; ?>">
                        <i class="fas fa-check"></i> Confirmadas
                    </a>
                    <a href="admin_doacoes.php?filtro=dinheiro"
                        class="filtro-btn <?php echo $filtro == 'dinheiro' ? 'ativo' : ''; ?>">
                        <i class="fas fa-money-bill"></i> Dinheiro
                    </a>
                    <a href="admin_doacoes.php?filtro=itens"
                        class="filtro-btn <?php echo $filtro == 'itens' ? 'ativo' : ''; ?>">
                        <i class="fas fa-box"></i> Itens
                    </a>
                </div>

                <?php if (count($doacoes) > 0): ?>
                <div class="lista-doacoes">
                    <?php foreach ($doacoes as $doacao): ?>
                    <div class="card-doacao <?php echo $doacao['status'] ?? 'pendente'; ?>">
                        <div class="card-doacao-conteudo">

                            <div class="card-doacao-icone <?php echo $doacao['tipo']; ?>">
                                <i
                                    class="fas <?php echo $doacao['tipo'] == 'dinheiro' ? 'fa-money-bill-wave' : 'fa-box-open'; ?>"></i>
                            </div>

                            <div class="card-doacao-info">
                                <h3>
                                    <?php echo $doacao['tipo'] == 'dinheiro' ? 'Doação em Dinheiro' : 'Doação de Item'; ?>
                                    <span class="badge-status <?php echo $doacao['status'] ?? 'pendente'; ?>">
                                        <?php echo ucfirst($doacao['status'] ?? 'pendente'); ?>
                                    </span>
                                </h3>

                                <?php if ($doacao['tipo'] == 'dinheiro'): ?>
                                <p><i class="fas fa-dollar-sign"></i> Valor: <span class="valor-destaque">R$
                                        <?php echo number_format($doacao['valor'], 2, ',', '.'); ?></span></p>
                                <?php else: ?>
                                <p><i class="fas fa-cubes"></i> Quantidade: <strong><?php echo $doacao['quantidade']; ?>
                                        item(ns)</strong></p>
                                <?php if ($doacao['data_agendamento']): ?>
                                <p><i class="fas fa-calendar-check"></i> Agendado:
                                    <strong><?php echo date('d/m/Y \à\s H:i', strtotime($doacao['data_agendamento'])); ?></strong>
                                </p>
                                <?php endif; ?>
                                <?php endif; ?>

                                <p><i class="fas fa-user"></i>
                                    <strong><?php echo htmlspecialchars($doacao['usuario_nome'] ?? 'Usuário removido'); ?></strong>
                                </p>
                                <p><i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($doacao['usuario_email'] ?? 'N/A'); ?></p>
                                <p><i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($doacao['usuario_telefone'] ?? 'Não informado'); ?></p>
                                <p><i class="fas fa-calendar"></i> Registrada em:
                                    <?php echo date('d/m/Y H:i', strtotime($doacao['data'])); ?></p>

                                <?php if (!empty($doacao['descricao'])): ?>
                                <p><i class="fas fa-comment"></i> <?php echo htmlspecialchars($doacao['descricao']); ?>
                                </p>
                                <?php endif; ?>

                                <?php if ($doacao['status'] == 'confirmado' && $doacao['data_confirmacao']): ?>
                                <p style="background: #d4edda; padding: 8px; border-radius: 6px; margin-top: 8px;">
                                    <i class="fas fa-check-circle" style="color: #155724;"></i>
                                    <strong>Confirmada em:</strong>
                                    <?php echo date('d/m/Y H:i', strtotime($doacao['data_confirmacao'])); ?>
                                </p>
                                <?php endif; ?>
                            </div>

                            <div class="card-doacao-acoes">
                                <?php if (($doacao['status'] ?? 'pendente') == 'pendente'): ?>
                                <button class="btn-confirmar"
                                    onclick="abrirModalConfirmar(<?php echo $doacao['id_doacao']; ?>, '<?php echo $doacao['tipo']; ?>')">
                                    <i class="fas fa-check"></i> Confirmar
                                </button>
                                <button class="btn-excluir"
                                    onclick="abrirModalExcluir(<?php echo $doacao['id_doacao']; ?>, '<?php echo addslashes($doacao['usuario_nome'] ?? 'este usuário'); ?>')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                                <?php else: ?>
                                <span style="color: #999; font-size: 13px; text-align: center;">
                                    Já processada
                                </span>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="vazio-doacoes">
                    <i class="fas fa-hand-holding-heart"></i>
                    <p>Nenhuma doação encontrada com este filtro.</p>
                </div>
                <?php endif; ?>

            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

    <!-- MODAL CONFIRMAR -->
    <div class="modal-confirmacao" id="modalConfirmar">
        <div class="modal-confirmacao-conteudo">
            <div class="icone-modal sucesso">
                <i class="fas fa-check"></i>
            </div>
            <h3>Confirmar Doação?</h3>
            <p id="textoConfirmar">Você confirma que esta doação foi recebida?</p>

            <form method="POST">
                <input type="hidden" name="id_doacao" id="idConfirmar">
                <input type="hidden" name="acao" value="confirmar">
                <div class="modal-botoes">
                    <button type="button" class="btn-cancelar" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="btn-confirmar-modal">Sim, Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EXCLUIR -->
    <div class="modal-confirmacao" id="modalExcluir">
        <div class="modal-confirmacao-conteudo">
            <div class="icone-modal perigo">
                <i class="fas fa-trash"></i>
            </div>
            <h3>Excluir Doação?</h3>
            <p id="textoExcluir">Tem certeza que deseja excluir esta doação? Esta ação não pode ser desfeita.</p>

            <form method="POST">
                <input type="hidden" name="id_doacao" id="idExcluir">
                <input type="hidden" name="acao" value="excluir">
                <div class="modal-botoes">
                    <button type="button" class="btn-cancelar" onclick="fecharModais()">Cancelar</button>
                    <button type="submit" class="btn-excluir-modal">Sim, Excluir</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirModalConfirmar(id, tipo) {
        document.getElementById('idConfirmar').value = id;

        var texto = document.getElementById('textoConfirmar');
        if (tipo == 'dinheiro') {
            texto.textContent = 'Você confirma que o PIX caiu na conta da ONG?';
        } else {
            texto.textContent = 'Você confirma que os itens foram recebidos?';
        }

        document.getElementById('modalConfirmar').style.display = 'flex';
    }

    function abrirModalExcluir(id, nome) {
        document.getElementById('idExcluir').value = id;
        document.getElementById('textoExcluir').textContent =
            'Tem certeza que deseja excluir esta doação? Esta ação não pode ser desfeita.';
        document.getElementById('modalExcluir').style.display = 'flex';
    }

    function fecharModais() {
        document.getElementById('modalConfirmar').style.display = 'none';
        document.getElementById('modalExcluir').style.display = 'none';
    }

    // Fechar modal clicando fora
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-confirmacao')) {
            fecharModais();
        }
    });

    // Fechar com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            fecharModais();
        }
    });
    </script>

</body>

</html>