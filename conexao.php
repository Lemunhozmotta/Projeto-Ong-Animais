<?php
// conexao.php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "ong";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8");

// ============================================
// FUNÇÕES PARA BUSCAR DADOS DO BANCO
// ============================================

function buscarAnimais($conexao)
{
    $sql = "SELECT a.*, 
                   CASE 
                       WHEN EXISTS (SELECT 1 FROM adocoes ad WHERE ad.id_animal = a.id_animal AND ad.status IN ('pendente', 'aprovado', 'concluido')) 
                       THEN 1 
                       ELSE 0 
                   END as adotado,
                   CASE 
                       WHEN EXISTS (SELECT 1 FROM adocoes ad WHERE ad.id_animal = a.id_animal AND ad.status IN ('pendente', 'aprovado', 'concluido')) 
                       THEN (SELECT ad.status FROM adocoes ad WHERE ad.id_animal = a.id_animal AND ad.status IN ('pendente', 'aprovado', 'concluido') ORDER BY ad.id_adocao DESC LIMIT 1)
                       ELSE NULL 
                   END as status_adocao
            FROM animais a 
            ORDER BY adotado ASC, a.data_acolhimento DESC";
    $resultado = $conexao->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function contarAnimais($conexao)
{
    $sql = "SELECT COUNT(*) as total FROM animais";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

function contarAdocoes($conexao)
{
    $sql = "SELECT COUNT(*) as total FROM adocoes";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

function totalDoacoes($conexao)
{
    $sql = "SELECT SUM(valor) as total FROM doacoes WHERE tipo = 'dinheiro'";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

function contarUsuarios($conexao)
{
    $sql = "SELECT COUNT(*) as total FROM usuarios";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

function buscarProjetos($conexao)
{
    return [
        ['id' => 1, 'nome' => 'Castração Solidária', 'descricao' => 'Programa de castração a preço acessível.', 'icone' => 'fa-stethoscope'],
        ['id' => 2, 'nome' => 'Lar Temporário', 'descricao' => 'Encontre um lar temporário para animais.', 'icone' => 'fa-home'],
        ['id' => 3, 'nome' => 'Educação Animal', 'descricao' => 'Programas educacionais sobre bem-estar animal.', 'icone' => 'fa-graduation-cap']
    ];
}

function buscarDepartamentos($conexao)
{
    $sql = "SELECT id_departamento, nome FROM departamentos ORDER BY nome";
    $resultado = $conexao->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function getUsuarioById($conexao, $id)
{
    $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

function getAnimalById($conexao, $id)
{
    $sql = "SELECT * FROM animais WHERE id_animal = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

function getAdocoesByUsuario($conexao, $usuario_id)
{
    $sql = "SELECT a.*, an.nome as animal_nome, an.especie, an.porte 
            FROM adocoes a 
            JOIN animais an ON a.id_animal = an.id_animal 
            WHERE a.id_usuario = ? 
            ORDER BY a.data DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

function getDoacoesByUsuario($conexao, $usuario_id)
{
    $sql = "SELECT * FROM doacoes WHERE id_usuario = ? ORDER BY data DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// FUNÇÕES DE PERMISSÃO
// ============================================

function getNivelUsuario($conexao, $usuario_id)
{
    $sql = "SELECT nivel FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();
    return $row['nivel'] ?? 4;
}

function getNomeNivel($nivel)
{
    $niveis = [
        1 => 'Administrador',
        2 => 'Gestor',
        3 => 'Voluntário',
        4 => 'Usuário'
    ];
    return $niveis[$nivel] ?? 'Usuário';
}

function podeAcessarAdmin($conexao, $usuario_id)
{
    $nivel = getNivelUsuario($conexao, $usuario_id);
    return $nivel <= 2;
}

function podeGerenciarAnimais($conexao, $usuario_id)
{
    $nivel = getNivelUsuario($conexao, $usuario_id);
    return $nivel <= 2;
}

function podeGerenciarUsuarios($conexao, $usuario_id)
{
    $nivel = getNivelUsuario($conexao, $usuario_id);
    return $nivel == 1;
}

function podeVerAdmin($conexao, $usuario_id)
{
    $nivel = getNivelUsuario($conexao, $usuario_id);
    return $nivel <= 2;
}

// ============================================
// FUNÇÕES DE UPLOAD
// ============================================

function salvarFotoAnimal($arquivo)
{
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($arquivo['type'], $tiposPermitidos)) {
        return null;
    }

    if ($arquivo['size'] > 5 * 1024 * 1024) {
        return null;
    }

    // Criar pasta se não existir
    $pastaDestino = 'uploads/animais/';
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeArquivo = uniqid('animal_') . '.' . $extensao;
    $caminhoDestino = $pastaDestino . $nomeArquivo;

    if (move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        return $caminhoDestino;
    }

    return null;
}

function adicionarAnimal($conexao, $dados, $foto)
{
    $nome = $dados['nome'];
    $especie = $dados['especie'];
    $porte = $dados['porte'];
    $data_acolhimento = $dados['data_acolhimento'];
    $idade_aparente = $dados['idade_aparente'];
    $saude = $dados['saude'];

    $caminhoFoto = salvarFotoAnimal($foto);

    if ($caminhoFoto === null) {
        $caminhoFoto = 'img/default-animal.jpg';
    }

    $sql = "INSERT INTO animais (nome, especie, porte, data_acolhimento, idade_aparente, saude, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssssss", $nome, $especie, $porte, $data_acolhimento, $idade_aparente, $saude, $caminhoFoto);

    if ($stmt->execute()) {
        return $conexao->insert_id;
    }
    return false;
}
function atualizarAnimal($conexao, $id, $dados, $foto = null)
{
    $nome = $dados['nome'];
    $especie = $dados['especie'];
    $porte = $dados['porte'];
    $data_acolhimento = $dados['data_acolhimento'];
    $idade_aparente = $dados['idade_aparente'];
    $saude = $dados['saude'];

    // Se enviou nova foto, salva
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $caminhoFoto = salvarFotoAnimal($foto);
        if ($caminhoFoto) {
            $sql = "UPDATE animais SET nome = ?, especie = ?, porte = ?, data_acolhimento = ?, idade_aparente = ?, saude = ?, foto = ? WHERE id_animal = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sssssssi", $nome, $especie, $porte, $data_acolhimento, $idade_aparente, $saude, $caminhoFoto, $id);
        } else {
            return false;
        }
    } else {
        // Sem nova foto
        $sql = "UPDATE animais SET nome = ?, especie = ?, porte = ?, data_acolhimento = ?, idade_aparente = ?, saude = ? WHERE id_animal = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssssi", $nome, $especie, $porte, $data_acolhimento, $idade_aparente, $saude, $id);
    }

    return $stmt->execute();
}

function excluirAnimal($conexao, $id)
{
    // Buscar o animal para saber a foto
    $animal = getAnimalById($conexao, $id);

    // Excluir a foto do servidor
    if ($animal && !empty($animal['foto']) && file_exists($animal['foto']) && strpos($animal['foto'], 'img/default') === false) {
        unlink($animal['foto']);
    }

    // Excluir do banco
    $sql = "DELETE FROM animais WHERE id_animal = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function buscarTodasAdocoes($conexao)
{
    $sql = "SELECT a.*, 
                   u.nome as usuario_nome, u.email as usuario_email, u.telefone as usuario_telefone,
                   an.nome as animal_nome, an.especie, an.porte, an.foto as animal_foto
            FROM adocoes a
            JOIN usuarios u ON a.id_usuario = u.id_usuario
            JOIN animais an ON a.id_animal = an.id_animal
            ORDER BY a.data DESC";
    $resultado = $conexao->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function atualizarStatusAdocao($conexao, $id_adocao, $novo_status, $observacoes = '')
{
    $sql = "UPDATE adocoes SET status = ?, observacoes = ? WHERE id_adocao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssi", $novo_status, $observacoes, $id_adocao);
    return $stmt->execute();
}

function contarAdocoesPendentes($conexao)
{
    $sql = "SELECT COUNT(*) as total FROM adocoes WHERE status = 'pendente'";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

// ============================================
// FUNÇÕES DE USUÁRIO (ADMIN)
// ============================================

function atualizarUsuario($conexao, $id, $dados)
{
    $nome = $dados['nome'];
    $email = $dados['email'];
    $telefone = $dados['telefone'] ?? '';
    $data_nascimento = $dados['data_nascimento'] ?? null;
    $endereco = $dados['endereco'] ?? '';
    $id_departamento = $dados['id_departamento'] ?? null;
    $nivel = $dados['nivel'] ?? 4;

    // Se tiver nova senha
    if (!empty($dados['nova_senha'])) {
        $senha_hash = password_hash($dados['nova_senha'], PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, data_nascimento = ?, endereco = ?, id_departamento = ?, nivel = ?, senha = ? WHERE id_usuario = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssssiisi", $nome, $email, $telefone, $data_nascimento, $endereco, $id_departamento, $nivel, $senha_hash, $id);
    } else {
        $sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, data_nascimento = ?, endereco = ?, id_departamento = ?, nivel = ? WHERE id_usuario = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sssssiii", $nome, $email, $telefone, $data_nascimento, $endereco, $id_departamento, $nivel, $id);
    }

    return $stmt->execute();
}

function excluirUsuario($conexao, $id)
{
    // Verificar se tem adoções
    $check_adocoes = $conexao->query("SELECT COUNT(*) as total FROM adocoes WHERE id_usuario = $id");
    $adocoes = $check_adocoes->fetch_assoc()['total'];

    // Verificar se tem doações
    $check_doacoes = $conexao->query("SELECT COUNT(*) as total FROM doacoes WHERE id_usuario = $id");
    $doacoes = $check_doacoes->fetch_assoc()['total'];

    $conexao->begin_transaction();

    try {
        // Excluir adoções primeiro
        if ($adocoes > 0) {
            $conexao->query("DELETE FROM adocoes WHERE id_usuario = $id");
        }

        // Excluir doações
        if ($doacoes > 0) {
            $conexao->query("DELETE FROM doacoes WHERE id_usuario = $id");
        }

        // Excluir usuário
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conexao->commit();
        return ['sucesso' => true, 'adocoes' => $adocoes, 'doacoes' => $doacoes];
    } catch (Exception $e) {
        $conexao->rollback();
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}

// ============================================
// FUNÇÕES DE DOAÇÕES
// ============================================

function buscarTodasDoacoes($conexao)
{
    $sql = "SELECT d.*, 
                   u.nome as usuario_nome, u.email as usuario_email, u.telefone as usuario_telefone
            FROM doacoes d
            LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
            ORDER BY d.data DESC";
    $resultado = $conexao->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

function contarDoacoesPendentes($conexao)
{
    $sql = "SELECT COUNT(*) as total FROM doacoes WHERE status = 'pendente'";
    $resultado = $conexao->query($sql);
    if ($resultado) {
        $row = $resultado->fetch_assoc();
        return $row['total'] ?? 0;
    }
    return 0;
}

function confirmarDoacao($conexao, $id_doacao, $id_admin)
{
    $sql = "UPDATE doacoes SET status = 'confirmado', confirmado_por = ?, data_confirmacao = NOW() WHERE id_doacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $id_admin, $id_doacao);
    return $stmt->execute();
}

function excluirDoacao($conexao, $id_doacao)
{
    $sql = "DELETE FROM doacoes WHERE id_doacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_doacao);
    return $stmt->execute();
}

function registrarDoacaoPendente($conexao, $id_usuario, $tipo, $valor, $quantidade, $descricao, $data_agendamento = null)
{
    $sql = "INSERT INTO doacoes (id_usuario, tipo, quantidade, valor, data, descricao, status, data_agendamento) 
            VALUES (?, ?, ?, ?, NOW(), ?, 'pendente', ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("isidss", $id_usuario, $tipo, $quantidade, $valor, $descricao, $data_agendamento);

    if ($stmt->execute()) {
        return $conexao->insert_id;
    }
    return false;
}