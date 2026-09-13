<!-- MODAL LOGIN/CADASTRO (reutilizável) -->
<div id="modalAcesso"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">

    <div
        style="background: white; padding: 30px; border-radius: 10px; max-width: 400px; width: 90%; position: relative; max-height: 90vh; overflow-y: auto;">

        <button onclick="fecharModal()"
            style="position: absolute; right: 15px; top: 10px; font-size: 30px; border: none; background: none; cursor: pointer;">
            &times;
        </button>

        <!-- LOGIN -->
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

        <!-- CADASTRO -->
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
                    <?php
                    if (isset($departamentos) && count($departamentos) > 0) {
                        foreach ($departamentos as $dept) {
                            echo '<option value="' . $dept['id_departamento'] . '">' . htmlspecialchars($dept['nome']) . '</option>';
                        }
                    }
                    ?>
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

        // Fechar com tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fecharModal();
            }
        });
    });
</script>