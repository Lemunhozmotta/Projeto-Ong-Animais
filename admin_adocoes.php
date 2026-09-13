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

if (!podeAcessarAdmin($conexao, $_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "❌ Você não tem permissão para acessar esta página!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

// Processar aprovação/rejeição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $id_adocao = $_POST['id_adocao'];
    $novo_status = $_POST['acao'];
    $observacoes = $_POST['observacoes'] ?? '';

    if (atualizarStatusAdocao($conexao, $id_adocao, $novo_status, $observacoes)) {
        $_SESSION['mensagem'] = "✅ Adoção " . ($novo_status == 'aprovado' ? 'aprovada' : 'rejeitada') . " com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "❌ Erro ao atualizar adoção.";
        $_SESSION['tipo_mensagem'] = "error";
    }
    header('Location: admin_adocoes.php');
    exit();
}

$adocoes = buscarTodasAdocoes($conexao);
$total_pendentes = contarAdocoesPendentes($conexao);
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_admin_adocoes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Gerenciar Adoções - APVAC</title>
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
            <div class="admin-adocoes-container">

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

                <div class="admin-adocoes-header">
                    <h1><i class="fas fa-heart"></i> Gerenciar Adoções</h1>
                    <?php if ($total_pendentes > 0): ?>
                    <span class="badge-pendente-count">
                        <i class="fas fa-clock"></i> <?php echo $total_pendentes; ?> pendente(s)
                    </span>
                    <?php endif; ?>
                </div>

                <?php if (count($adocoes) > 0): ?>
                <div class="lista-adocoes">
                    <?php foreach ($adocoes as $adocao): ?>
                    <div class="card-adocao <?php echo $adocao['status'] ?? 'pendente'; ?>">
                        <div class="card-adocao-conteudo">

                            <div class="card-adocao-foto">
                                <?php if (!empty($adocao['animal_foto']) && file_exists($adocao['animal_foto'])): ?>
                                <img src="<?php echo $adocao['animal_foto']; ?>"
                                    alt="<?php echo $adocao['animal_nome']; ?>">
                                <?php else: ?>
                                <i class="fas <?php echo $adocao['especie'] == 'Cão' ? 'fa-dog' : 'fa-cat'; ?>"></i>
                                <?php endif; ?>
                            </div>

                            <div class="card-adocao-info">
                                <h3><?php echo htmlspecialchars($adocao['animal_nome']); ?>
                                    <span class="badge-status <?php echo $adocao['status'] ?? 'pendente'; ?>">
                                        <?php echo ucfirst($adocao['status'] ?? 'pendente'); ?>
                                    </span>
                                </h3>
                                <p><i class="fas fa-user"></i>
                                    <strong><?php echo htmlspecialchars($adocao['usuario_nome']); ?></strong>
                                </p>
                                <p><i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($adocao['usuario_email']); ?></p>
                                <p><i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($adocao['usuario_telefone'] ?? 'Não informado'); ?></p>
                                <p><i class="fas fa-calendar"></i> Solicitado em:
                                    <?php echo date('d/m/Y', strtotime($adocao['data'])); ?></p>
                                <?php if (!empty($adocao['descricao'])): ?>
                                <p><i class="fas fa-comment"></i> <?php echo htmlspecialchars($adocao['descricao']); ?>
                                </p>
                                <?php endif; ?>
                                <?php if (!empty($adocao['observacoes'])): ?>
                                <p style="background: #f8f9fa; padding: 8px; border-radius: 6px; margin-top: 8px;">
                                    <i class="fas fa-sticky-note"></i> <strong>Obs:</strong>
                                    <?php echo htmlspecialchars($adocao['observacoes']); ?>
                                </p>
                                <?php endif; ?>
                            </div>

                            <div class="card-adocao-acoes">
                                <?php if (($adocao['status'] ?? 'pendente') == 'pendente'): ?>
                                <button class="btn-aprovar"
                                    onclick="abrirModal(<?php echo $adocao['id_adocao']; ?>, 'aprovado')">
                                    <i class="fas fa-check"></i> Aprovar
                                </button>
                                <button class="btn-rejeitar"
                                    onclick="abrirModal(<?php echo $adocao['id_adocao']; ?>, 'rejeitado')">
                                    <i class="fas fa-times"></i> Rejeitar
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
                <div class="vazio-adocoes">
                    <i class="fas fa-heart"></i>
                    <p>Nenhuma adoção solicitada ainda.</p>
                </div>
                <?php endif; ?>

            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

    <!-- MODAL OBSERVAÇÃO -->
    <div class="modal-observacao" id="modalObservacao">
        <div class="modal-observacao-conteudo">
            <h3 id="tituloModal">Confirmar Ação</h3>
            <form method="POST">
                <input type="hidden" name="id_adocao" id="idAdocaoModal">
                <input type="hidden" name="acao" id="acaoModal">

                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Observações (opcional):</label>
                <textarea name="observacoes" placeholder="Adicione uma observação sobre esta adoção..."></textarea>

                <div class="modal-botoes">
                    <button type="button" onclick="fecharModal()" style="background: #6c757d; color: white;">
                        Cancelar
                    </button>
                    <button type="submit" id="btnConfirmar" style="background: #28a745; color: white;">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirModal(id, acao) {
        document.getElementById('idAdocaoModal').value = id;
        document.getElementById('acaoModal').value = acao;

        var titulo = document.getElementById('tituloModal');
        var btnConfirmar = document.getElementById('btnConfirmar');

        if (acao == 'aprovado') {
            titulo.textContent = '✅ Aprovar Adoção';
            btnConfirmar.style.background = '#28a745';
            btnConfirmar.textContent = 'Aprovar';
        } else {
            titulo.textContent = '❌ Rejeitar Adoção';
            btnConfirmar.style.background = '#dc3545';
            btnConfirmar.textContent = 'Rejeitar';
        }

        document.getElementById('modalObservacao').style.display = 'flex';
    }

    function fecharModal() {
        document.getElementById('modalObservacao').style.display = 'none';
    }
    </script>

</body>

</html>