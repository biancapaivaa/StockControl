<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: produtos.php");
    exit;
}

$produto_id = (int)($_POST['produto_id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
$avaliacao = (int)($_POST['avaliacao'] ?? 0);

// Validações
$erros = [];

if ($produto_id <= 0) {
    $erros[] = "Produto inválido.";
}

if (empty($nome)) {
    $erros[] = "Nome é obrigatório.";
} elseif (strlen($nome) > 120) {
    $erros[] = "Nome não pode ter mais de 120 caracteres.";
}

if (empty($email)) {
    $erros[] = "E-mail é obrigatório.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "E-mail inválido.";
} elseif (strlen($email) > 180) {
    $erros[] = "E-mail não pode ter mais de 180 caracteres.";
}

if (empty($comentario)) {
    $erros[] = "Comentário é obrigatório.";
}

if ($avaliacao < 0 || $avaliacao > 5) {
    $avaliacao = 0;
}

// Verificar se produto existe
if ($produto_id > 0) {
    $stmt = $pdo->prepare("SELECT id FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    if (!$stmt->fetch()) {
        $erros[] = "Produto não encontrado.";
    }
}

if (!empty($erros)) {
    // Redirecionar com erro
    $erroMsg = implode(" ", $erros);
    header("Location: form_comentario.php?produto_id=$produto_id&erro=" . urlencode($erroMsg));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO comentarios (produto_id, nome, email, comentario, avaliacao, status)
        VALUES (?, ?, ?, ?, ?, 'Pendente')
    ");
    $stmt->execute([$produto_id, $nome, $email, $comentario, $avaliacao]);
    
    header("Location: form_comentario.php?produto_id=$produto_id&sucesso=1");
    exit;
} catch (PDOException $e) {
    header("Location: form_comentario.php?produto_id=$produto_id&erro=" . urlencode("Erro ao salvar comentário."));
    exit;
}
