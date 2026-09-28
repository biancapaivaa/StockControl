<?php
require_once 'auth.php';
require_once 'conexao.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: comentarios.php?erro=" . urlencode("Comentário não encontrado."));
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE comentarios SET status = 'Rejeitado' WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: comentarios.php?mensagem=" . urlencode("Comentário rejeitado com sucesso!"));
    exit;
} catch (PDOException $e) {
    header("Location: comentarios.php?erro=" . urlencode("Erro ao rejeitar comentário."));
    exit;
}
