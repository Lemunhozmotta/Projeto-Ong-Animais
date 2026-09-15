<?php
session_start();
include "conexao.php";

$total_pendentes = 0;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] <= 2) {
    $total_pendentes = contarAdocoesPendentes($conexao);
}

$animais = buscarAnimais($conexao);
$total_animais = contarAnimais($conexao);
$total_adocoes = contarAdocoes($conexao);
$total_doacoes = totalDoacoes($conexao);
$total_voluntarios = contarUsuarios($conexao);
$projetos = buscarProjetos($conexao);
$departamentos = buscarDepartamentos($conexao);

$mensagem = '';
$tipo_mensagem = '';

if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'] ?? 'success';
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
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
    <link rel="stylesheet" href="estilos/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../Projeto-Ong-Animais/img/logoONG-icon.ico">
    <title>APVAC - Associação Proteção à Vida Animal Cubatão</title>
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

            <?php if ($mensagem): ?>
            <div
                style="max-width: 800px; margin: 20px auto; padding: 15px; border-radius: 8px; text-align: center; 
                    <?php echo $tipo_mensagem == 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                <?php echo $mensagem; ?>
            </div>
            <?php endif; ?>

            <section class="module parallax parallax1">
                <h1>Proteção e cuidado aos animais</h1>
            </section>

            <section class="module content">
                <div class="container">
                    <h2>Ajude a transformar vidas</h2>
                    <p>Nosso trabalho é dedicado à proteção, cuidado e bem-estar dos animais que precisam de ajuda.</p>
                    <p>Faça parte dessa causa e ajude a construir uma vida melhor para nossos animais.</p>
                </div>
                <div class="video">
                    <p>VIDEO AQUI!</p>
                </div>
            </section>

            <section class="module parallax parallax2">
                <h1>Amor que transforma</h1>
            </section>

            <section class="module content">
                <div class="container">
                    <h2>Adote um animal</h2>
                    <p>Conheça nossos animais e encontre um novo companheiro para fazer parte da sua família.</p>
                    <p>Cada adoção representa uma nova oportunidade de vida.</p>

                    <!-- Filtros -->
                    <div class="filtros-animais">
                        <button class="filtro-animal ativo" onclick="filtrarAnimais('todos', this)">
                            <i class="fas fa-list"></i> Todos
                        </button>
                        <button class="filtro-animal" onclick="filtrarAnimais('disponivel', this)">
                            <i class="fas fa-heart"></i> Disponíveis
                        </button>
                        <button class="filtro-animal" onclick="filtrarAnimais('adotado', this)">
                            <i class="fas fa-check"></i> Adotados
                        </button>
                    </div>

                    <?php if (count($animais) > 0): ?>
                    <div class="cards-grid" id="cardsAnimais">
                        <?php foreach ($animais as $animal): ?>
                        <div class="card-animal <?php echo $animal['adotado'] ? 'animal-adotado' : ''; ?>"
                            data-status="<?php echo $animal['adotado'] ? 'adotado' : 'disponivel'; ?>">
                            <div class="foto-animal">
                                <?php if (!empty($animal['foto']) && file_exists($animal['foto'])): ?>
                                <img src="<?php echo $animal['foto']; ?>"
                                    alt="<?php echo htmlspecialchars($animal['nome']); ?>">
                                <?php else: ?>
                                <i class="fas <?php echo $animal['especie'] == 'Cão' ? 'fa-dog' : 'fa-cat'; ?>"></i>
                                <?php endif; ?>

                                <?php if ($animal['adotado']): ?>
                                <div class="selo-adotado">
                                    <i class="fas fa-check-circle"></i> ADOTADO
                                </div>
                                <?php else: ?>
                                <div class="selo-disponivel">
                                    <i class="fas fa-heart"></i> DISPONÍVEL
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="info">
                                <h3><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                <div class="detalhes">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($animal['especie']); ?>
                                </div>
                                <div class="detalhes">
                                    <i class="fas fa-ruler"></i> <?php echo htmlspecialchars($animal['porte']); ?>
                                    <span style="margin-left: 10px;">
                                        <i class="fas fa-baby"></i>
                                        <?php echo htmlspecialchars($animal['idade_aparente']); ?>
                                    </span>
                                </div>
                                <div class="detalhes">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($animal['data_acolhimento'])); ?>
                                </div>
                                <div class="descricao">
                                    <i class="fas fa-heartbeat"></i>
                                    <?php echo htmlspecialchars($animal['saude']); ?>
                                </div>

                                <?php if ($animal['adotado']): ?>
                                <button class="btn-adotar btn-adotado" disabled>
                                    <i class="fas fa-check"></i> Já Adotado
                                </button>
                                <?php elseif ($usuario_logado): ?>
                                <a href="adotar.php?id=<?php echo $animal['id_animal']; ?>" class="btn-adotar">
                                    <i class="fas fa-heart"></i> Quero Adotar
                                </a>
                                <?php else: ?>
                                <button onclick="abrirModal()" class="btn-adotar">
                                    <i class="fas fa-heart"></i> Faça login para adotar
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; font-size: 18px; padding: 40px 0;">No momento não temos animais
                        disponíveis para adoção. Volte em breve!</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="module parallax parallax3">
                <h1>Faça parte dessa causa</h1>
            </section>

            <section class="module content">
                <div class="container">
                    <h2>Participe da APVAC</h2>
                    <p>Seja como voluntário, adotante ou doador, sua participação pode fazer a diferença.</p>
                </div>
            </section>

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
                        <?php foreach ($departamentos as $dept): ?>
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

    <script>
    function filtrarAnimais(filtro, botao) {
        // Atualizar botão ativo
        document.querySelectorAll('.filtro-animal').forEach(function(btn) {
            btn.classList.remove('ativo');
        });
        botao.classList.add('ativo');

        // Filtrar cards
        var cards = document.querySelectorAll('#cardsAnimais .card-animal');
        cards.forEach(function(card) {
            var status = card.getAttribute('data-status');

            if (filtro === 'todos') {
                card.style.display = 'flex';
            } else if (filtro === status) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
    </script>

</body>

</html>