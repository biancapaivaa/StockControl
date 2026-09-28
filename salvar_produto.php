<?php
require_once 'auth.php';
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produtos.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$preco = (float)($_POST['preco'] ?? 0);
$preco_promocional = (float)($_POST['preco_promocional'] ?? 0);
$estoque = (int)($_POST['estoque'] ?? 0);
$sku = trim($_POST['sku'] ?? '');
$status = $_POST['status'] ?? 'Ativo';

if ($nome === '' || $descricao === '' || $categoria === '' || $preco < 0 || $preco_promocional < 0 || $estoque < 0 || !in_array($status, ['Ativo', 'Inativo'], true)) {
    header('Location: novo_produto.php?erro=' . urlencode('Preencha os dados obrigatórios corretamente.'));
    exit;
}

if ($preco_promocional > 0 && $preco_promocional > $preco) {
    header('Location: novo_produto.php?erro=' . urlencode('O preço promocional não pode ser maior que o preço normal.'));
    exit;
}

if ($sku !== '') {
    $stmt = $pdo->prepare("SELECT id FROM produtos WHERE sku = ?");
    $stmt->execute([$sku]);
    if ($stmt->fetch()) {
        header('Location: novo_produto.php?erro=' . urlencode('Este SKU já está cadastrado.'));
        exit;
    }
}

$arquivos = $_FILES['imagens'] ?? null;
$permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$imagensValidas = [];

if ($arquivos && isset($arquivos['name']) && is_array($arquivos['name'])) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($arquivos['name'] as $i => $nomeOriginal) {
        if ($arquivos['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($arquivos['error'][$i] !== UPLOAD_ERR_OK || $arquivos['size'][$i] > 5 * 1024 * 1024) {
            header('Location: novo_produto.php?erro=' . urlencode('Uma das imagens é inválida ou ultrapassa 5 MB.'));
            exit;
        }
        $tmp = $arquivos['tmp_name'][$i];
        $mime = $finfo->file($tmp);
        if (!isset($permitidos[$mime])) {
            header('Location: novo_produto.php?erro=' . urlencode('Formato de imagem não permitido.'));
            exit;
        }
        $imagensValidas[] = ['tmp' => $tmp, 'ext' => $permitidos[$mime]];
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, categoria, preco, preco_promocional, estoque, sku, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $descricao, $categoria, $preco, $preco_promocional, $estoque, $sku ?: null, $status]);
    $produtoId = (int)$pdo->lastInsertId();

    $pasta = __DIR__ . '/uploads/produtos';
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);

    $primeiraImagem = null;
    foreach ($imagensValidas as $img) {
        $nomeArquivo = bin2hex(random_bytes(12)) . '.' . $img['ext'];
        $destino = $pasta . '/' . $nomeArquivo;
        if (!move_uploaded_file($img['tmp'], $destino)) {
            throw new RuntimeException('Falha ao salvar uma imagem.');
        }
        $caminhoBanco = 'uploads/produtos/' . $nomeArquivo;
        if ($primeiraImagem === null) $primeiraImagem = $caminhoBanco;
        $stmtImg = $pdo->prepare("INSERT INTO produto_imagens (produto_id, imagem) VALUES (?, ?)");
        $stmtImg->execute([$produtoId, $caminhoBanco]);
    }

    if ($primeiraImagem !== null) {
        $stmt = $pdo->prepare("UPDATE produtos SET imagem_principal = ? WHERE id = ?");
        $stmt->execute([$primeiraImagem, $produtoId]);
    }

    $pdo->commit();
    header('Location: produtos.php?mensagem=' . urlencode('Produto cadastrado com sucesso.'));
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: novo_produto.php?erro=' . urlencode('Não foi possível cadastrar o produto.'));
    exit;
}
?>