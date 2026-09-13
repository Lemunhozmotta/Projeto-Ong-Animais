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
    $_SESSION['mensagem'] = "Faça login para fazer uma doação!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

// Processar doação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $tipo = $_POST['tipo'];
    $valor = !empty($_POST['valor']) ? $_POST['valor'] : null;
    $quantidade = !empty($_POST['quantidade']) ? $_POST['quantidade'] : null;
    $descricao = $_POST['descricao'] ?? '';
    $data_agendamento = !empty($_POST['data_agendamento']) ? $_POST['data_agendamento'] : null;

    if ($tipo == 'dinheiro' && empty($valor)) {
        $_SESSION['mensagem'] = "Informe o valor da doação!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: doar.php');
        exit();
    }

    if ($tipo == 'item' && empty($quantidade)) {
        $_SESSION['mensagem'] = "Informe a quantidade de itens!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: doar.php');
        exit();
    }

    $id_doacao = registrarDoacaoPendente($conexao, $usuario_id, $tipo, $valor, $quantidade, $descricao, $data_agendamento);

    if ($id_doacao) {
        // Redirecionar para a mesma página com o ID da doação para mostrar o modal
        header("Location: doar.php?sucesso=$id_doacao");
        exit();
    } else {
        $_SESSION['mensagem'] = "❌ Erro ao processar doação.";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: doar.php');
        exit();
    }
}

$usuario_logado = true;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'visitante';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 4;
$nome_nivel = getNomeNivel($nivel_usuario);
$total_pendentes = contarAdocoesPendentes($conexao);

// Dados para o modal de sucesso
$doacao_sucesso = null;
if (isset($_GET['sucesso'])) {
    $id = $_GET['sucesso'];
    $sql = "SELECT * FROM doacoes WHERE id_doacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $doacao_sucesso = $resultado->fetch_assoc();
}

// CONFIGURAÇÕES DA ONG (troque pelos dados reais depois)
$chave_pix = "apvac.projeto@protonmail.com"; // Troque pela chave real da ONG
$nome_recebedor = "APVAC - Associação Proteção à Vida Animal Cubatão";
$endereco_ong = "Rua das Flores, 123 - Centro - Cubatão/SP";
$horario_ong = "Segunda a Sexta: 9h às 18h | Sábado: 9h às 13h";

// Gerar QR Code PIX (usando API gratuita)
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($chave_pix);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style_geral.css">
    <link rel="stylesheet" href="estilos/style_doar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Fazer Doação - APVAC</title>
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
                    <li><a href="doar.php" class="ativo">Doar</a></li>
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
                <span>Olá, <?php echo htmlspecialchars($nome_usuario); ?>! 👋</span>
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
            <div class="doar-container">
                <a href="javascript:history.back()" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>

                <div class="doar-box">
                    <h1><i class="fas fa-hand-holding-heart"></i> Fazer Doação</h1>
                    <p>Escolha como deseja contribuir com a APVAC.</p>

                    <?php if (isset($_SESSION['mensagem'])): ?>
                    <div
                        style="padding: 12px; background: <?php echo $_SESSION['tipo_mensagem'] == 'success' ? '#d4edda' : '#f8d7da'; ?>; border-radius: 5px; margin-bottom: 20px; color: <?php echo $_SESSION['tipo_mensagem'] == 'success' ? '#155724' : '#721c24'; ?>;">
                        <?php
                            echo $_SESSION['mensagem'];
                            unset($_SESSION['mensagem']);
                            unset($_SESSION['tipo_mensagem']);
                            ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>Tipo de Doação</label>
                            <select name="tipo" id="tipoDoacao" required>
                                <option value="">Selecione...</option>
                                <option value="dinheiro">💵 Dinheiro (PIX)</option>
                                <option value="item">📦 Item (ração, medicamentos, etc)</option>
                            </select>
                        </div>

                        <div class="form-group campo-dinheiro" id="campoDinheiro">
                            <label>Valor (R$)</label>
                            <input type="number" step="0.01" min="1" name="valor" placeholder="Ex: 50.00">
                        </div>

                        <div class="form-group campo-item" id="campoItem">
                            <label>Quantidade</label>
                            <input type="number" min="1" name="quantidade" placeholder="Ex: 2">
                        </div>

                        <div class="form-group campo-item" id="campoAgendamento">
                            <label>Data e Hora para Entrega</label>
                            <input type="datetime-local" name="data_agendamento">
                            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Escolha quando deseja entregar os itens na ONG
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Descrição (opcional)</label>
                            <textarea name="descricao" placeholder="Descreva sua doação..."></textarea>
                        </div>

                        <button type="submit" class="btn-doar">
                            <i class="fas fa-heart"></i> Registrar Doação
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <footer>
            <p>APVAC - Associação Proteção à Vida Animal Cubatão</p>
        </footer>
    </div>

    <!-- MODAL DE SUCESSO (APARECE APÓS REGISTRAR) -->
    <?php if ($doacao_sucesso): ?>
    <div id="modalSucesso"
        style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">

        <div
            style="background: white; padding: 35px; border-radius: 16px; max-width: 550px; width: 100%; position: relative; max-height: 90vh; overflow-y: auto;">

            <button onclick="window.location.href='doar.php'"
                style="position: absolute; right: 15px; top: 10px; font-size: 30px; border: none; background: none; cursor: pointer;">
                &times;
            </button>

            <div style="text-align: center; margin-bottom: 25px;">
                <div
                    style="background: #d4edda; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-check" style="font-size: 2.5rem; color: #28a745;"></i>
                </div>
                <h2 style="color: #1a1a2e; margin-bottom: 8px;">Doação Registrada!</h2>
                <p style="color: #666;">Sua intenção de doação foi salva com sucesso.</p>
            </div>

            <?php if ($doacao_sucesso['tipo'] == 'dinheiro'): ?>
            <!-- DOAÇÃO EM DINHEIRO -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <h3 style="color: #008b8b; margin-bottom: 15px; text-align: center;">
                    <i class="fas fa-qrcode"></i> Pague com PIX
                </h3>

                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="<?php echo $qr_code_url; ?>" alt="QR Code PIX"
                        style="border-radius: 12px; border: 3px solid #008b8b; padding: 10px; background: white;">
                </div>

                <div style="text-align: center; margin-bottom: 15px;">
                    <p style="color: #666; font-size: 13px; margin-bottom: 5px;">Ou copie a chave PIX:</p>
                    <div
                        style="background: white; padding: 12px; border-radius: 8px; border: 2px dashed #008b8b; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <code id="chavePix"
                            style="font-size: 13px; color: #1a1a2e; word-break: break-all; flex: 1; text-align: left;">
                                    <?php echo $chave_pix; ?>
                                </code>
                        <button onclick="copiarPix()"
                            style="background: #008b8b; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; white-space: nowrap;">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                </div>

                <div style="background: #fff3cd; padding: 12px; border-radius: 8px; font-size: 13px; color: #856404;">
                    <p style="margin: 0 0 8px 0;">
                        <strong>Valor:</strong> R$ <?php echo number_format($doacao_sucesso['valor'], 2, ',', '.'); ?>
                    </p>
                    <p style="margin: 0 0 8px 0;">
                        <strong>Recebedor:</strong> <?php echo $nome_recebedor; ?>
                    </p>
                    <p style="margin: 0;">
                        <i class="fas fa-info-circle"></i> Após o PIX, a ONG irá confirmar sua doação.
                    </p>
                </div>
            </div>

            <?php else: ?>
            <!-- DOAÇÃO DE ITENS -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <h3 style="color: #008b8b; margin-bottom: 15px; text-align: center;">
                    <i class="fas fa-box-open"></i> Entrega dos Itens
                </h3>

                <div style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                    <p style="margin: 0 0 10px 0; color: #1a1a2e;">
                        <i class="fas fa-map-marker-alt" style="color: #008b8b;"></i>
                        <strong>Endereço:</strong>
                    </p>
                    <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
                        <?php echo $endereco_ong; ?>
                    </p>

                    <p style="margin: 0 0 10px 0; color: #1a1a2e;">
                        <i class="fas fa-clock" style="color: #008b8b;"></i>
                        <strong>Horário:</strong>
                    </p>
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        <?php echo $horario_ong; ?>
                    </p>
                </div>

                <?php if ($doacao_sucesso['data_agendamento']): ?>
                <div
                    style="background: #d1ecf1; padding: 12px; border-radius: 8px; font-size: 13px; color: #0c5460; margin-bottom: 15px;">
                    <i class="fas fa-calendar-check"></i>
                    <strong>Agendado para:</strong>
                    <?php echo date('d/m/Y \à\s H:i', strtotime($doacao_sucesso['data_agendamento'])); ?>
                </div>
                <?php endif; ?>

                <div style="background: #fff3cd; padding: 12px; border-radius: 8px; font-size: 13px; color: #856404;">
                    <p style="margin: 0 0 8px 0;">
                        <strong>Itens:</strong> <?php echo $doacao_sucesso['quantidade']; ?>
                    </p>
                    <?php if ($doacao_sucesso['descricao']): ?>
                    <p style="margin: 0 0 8px 0;">
                        <strong>Descrição:</strong> <?php echo htmlspecialchars($doacao_sucesso['descricao']); ?>
                    </p>
                    <?php endif; ?>
                    <p style="margin: 0;">
                        <i class="fas fa-info-circle"></i> Após a entrega, a ONG irá confirmar sua doação.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <div style="display: flex; gap: 10px;">
                <a href="perfil.php"
                    style="flex: 1; background: #008b8b; color: white; text-align: center; padding: 14px; border-radius: 10px; text-decoration: none; font-weight: 700;">
                    <i class="fas fa-user"></i> Ver Minhas Doações
                </a>
                <a href="doar.php"
                    style="flex: 1; background: #6c757d; color: white; text-align: center; padding: 14px; border-radius: 10px; text-decoration: none; font-weight: 700;">
                    <i class="fas fa-plus"></i> Nova Doação
                </a>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <script>
    document.getElementById('tipoDoacao').addEventListener('change', function() {
        var campoDinheiro = document.getElementById('campoDinheiro');
        var campoItem = document.getElementById('campoItem');
        var campoAgendamento = document.getElementById('campoAgendamento');

        if (this.value == 'dinheiro') {
            campoDinheiro.classList.add('ativo');
            campoItem.classList.remove('ativo');
            campoAgendamento.classList.remove('ativo');
        } else if (this.value == 'item') {
            campoItem.classList.add('ativo');
            campoAgendamento.classList.add('ativo');
            campoDinheiro.classList.remove('ativo');
        } else {
            campoDinheiro.classList.remove('ativo');
            campoItem.classList.remove('ativo');
            campoAgendamento.classList.remove('ativo');
        }
    });

    function copiarPix() {
        var chave = document.getElementById('chavePix').textContent.trim();
        navigator.clipboard.writeText(chave).then(function() {
            alert('✅ Chave PIX copiada!');
        }).catch(function() {
            // Fallback para navegadores antigos
            var range = document.createRange();
            range.selectNode(document.getElementById('chavePix'));
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            alert('✅ Chave PIX copiada!');
        });
    }
    </script>

</body>

</html>