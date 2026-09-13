<?php
session_start();
include "conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifica se os campos obrigatórios foram enviados
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha'])) {
        $_SESSION['mensagem'] = "Preencha todos os campos obrigatórios!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: index.php');
        exit();
    }

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $telefone = $_POST['telefone'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    $endereco = $_POST['endereco'] ?? '';
    $id_departamento = $_POST['id_departamento'] ?? null;

    // Verifica se o departamento foi selecionado
    if (empty($id_departamento)) {
        $_SESSION['mensagem'] = "Selecione um departamento!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: index.php');
        exit();
    }

    // Verificar se email já existe
    $check_sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $check_stmt = $conexao->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['mensagem'] = "Este email já está cadastrado!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: index.php');
        exit();
    }

    // Criptografar senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir
    $sql = "INSERT INTO usuarios (nome, email, senha, telefone, data_nascimento, endereco, id_departamento) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssssssi", $nome, $email, $senha_hash, $telefone, $data_nascimento, $endereco, $id_departamento);

    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "✅ Cadastro realizado com sucesso! Faça login para continuar.";
        $_SESSION['tipo_mensagem'] = "success";
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['mensagem'] = "❌ Erro ao cadastrar: " . $stmt->error;
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
