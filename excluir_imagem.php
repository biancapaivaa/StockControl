<?php
require_once 'auth.php';
require_once 'conexao.php';

$id = (int)($_GET['id'] ?? 0);
$produtoId = (int)($_GET['produto_id'] ?? 0);

if ($id <= 0 || $produtoId <= 0) {
    header('Location: produtos.php');
    exit;
}

$stmt = $pdo->prepare("SELECT imagem FROM produto_imagens WHERE id = ? AND produto_id = ?");
$stmt->execute([$id, $produtoId]);
$imagem = $stmt->fetchColumn();

if (!$imagem) {
    header('Location: editar_produto.php?id=' . $produtoId);
    exit;
}

$stmt = $pdo->prepare("SELECT imagem_principal FROM produtos WHERE id = ?");
$stmt->execute([$produtoId]);
$principal = $stmt->fetchColumn();

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("DELETE FROM produto_imagens WHERE id = ? AND produto_id = ?");
    $stmt->execute([$id, $produtoId]);

    if ($principal === $imagem) {
        $stmt = $pdo->prepare("SELECT imagem FROM produto_imagens WHERE produto_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$produtoId]);
        $novaPrincipal = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE produtos SET imagem_principal = ? WHERE id = ?");
        $stmt->execute([$novaPrincipal ?: null, $produtoId]);
    }

    $pdo->commit();

    $arquivo = __DIR__ . '/' . $imagem;
    if (is_file($arquivo)) @unlink($arquivo);

    header('Location: editar_produto.php?id=' . $produtoId);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: editar_produto.php?id=' . $produtoId . '&erro=' . urlencode('Não foi possível remover a imagem.'));
    exit;
}
?>