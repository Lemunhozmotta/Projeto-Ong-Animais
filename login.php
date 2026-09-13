<?php
session_start();
include "conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_nivel'] = $usuario['nivel'] ?? 4;
        $_SESSION['usuario_departamento'] = $usuario['id_departamento'];

        $_SESSION['mensagem'] = "Bem-vindo(a), " . $usuario['nome'] . "!";
        $_SESSION['tipo_mensagem'] = "success";
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['mensagem'] = "Email ou senha inválidos!";
        $_SESSION['tipo_mensagem'] = "error";
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
