<?php
session_start();
include "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = "Você precisa estar logado para adotar um animal!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = "Animal não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

$animal_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT * FROM animais WHERE id_animal = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $animal_id);
$stmt->execute();
$resultado = $stmt->get_result();
$animal = $resultado->fetch_assoc();

if (!$animal) {
    $_SESSION['mensagem'] = "Animal não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

$check_sql = "SELECT * FROM adocoes WHERE id_animal = ? AND status IN ('pendente', 'aprovado', 'concluido')";
$check_stmt = $conexao->prepare($check_sql);
$check_stmt->bind_param("i", $animal_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['mensagem'] = "Este animal já foi adotado!";
    $_SESSION['tipo_mensagem'] = "error";
    header('Location: index.php');
    exit();
}

$sql = "INSERT INTO adocoes (id_usuario, id_animal, data, descricao) 
        VALUES (?, ?, NOW(), 'Solicitação de adoção')";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $animal_id);

if ($stmt->execute()) {
    $_SESSION['mensagem'] = "Adoção solicitada com sucesso! Entraremos em contato em breve.";
    $_SESSION['tipo_mensagem'] = "success";
} else {
    $_SESSION['mensagem'] = "Erro ao processar adoção. Tente novamente.";
    $_SESSION['tipo_mensagem'] = "error";
}

header('Location: index.php');
exit();