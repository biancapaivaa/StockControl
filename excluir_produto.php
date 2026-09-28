<?php
require_once 'auth.php';
require_once 'conexao.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: produtos.php?erro=' . urlencode('Produto inválido.'));
    exit;
}

$stmt = $pdo->prepare("SELECT imagem_principal FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    header('Location: produtos.php?erro=' . urlencode('Produto não encontrado.'));
    exit;
}

$stmt = $pdo->prepare("SELECT imagem FROM produto_imagens WHERE produto_id = ?");
$stmt->execute([$id]);
$imagens = $stmt->fetchAll();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM produto_imagens WHERE produto_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    foreach ($imagens as $img) {
        $arquivo = __DIR__ . '/' . $img['imagem'];
        if (is_file($arquivo)) @unlink($arquivo);
    }

    if (!empty($produto['imagem_principal'])) {
        $arquivo = __DIR__ . '/' . $produto['imagem_principal'];
        if (is_file($arquivo)) @unlink($arquivo);
    }

    header('Location: produtos.php?mensagem=' . urlencode('Produto excluído com sucesso.'));
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: produtos.php?erro=' . urlencode('Não foi possível excluir o produto.'));
    exit;
}
?>