<?php
require_once 'auth.php';
require_once 'conexao.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: produtos.php?erro=' . urlencode('Produto inválido.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        header('Location: produtos.php?erro=' . urlencode('Produto não encontrado.'));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM produto_imagens WHERE produto_id = ? ORDER BY id ASC");
    $stmt->execute([$id]);
    $imagens = $stmt->fetchAll();

    $erro = $_GET['erro'] ?? '';
    require 'form_produto.php';
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
    header('Location: editar_produto.php?id=' . $id . '&erro=' . urlencode('Preencha os dados obrigatórios corretamente.'));
    exit;
}

if ($preco_promocional > 0 && $preco_promocional > $preco) {
    header('Location: editar_produto.php?id=' . $id . '&erro=' . urlencode('O preço promocional não pode ser maior que o preço normal.'));
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM produtos WHERE sku = ? AND id <> ?");
$stmt->execute([$sku ?: null, $id]);
if ($sku !== '' && $stmt->fetch()) {
    header('Location: editar_produto.php?id=' . $id . '&erro=' . urlencode('Este SKU já está cadastrado em outro produto.'));
    exit;
}

$arquivos = $_FILES['imagens'] ?? null;
$permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$imagensValidas = [];

if ($arquivos && isset($arquivos['name']) && is_array($arquivos['name'])) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($arquivos['name'] as $i => $nomeOriginal) {
        if ($arquivos['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($arquivos['error'][$i] !== UPLOAD_ERR_OK || $arquivos['size'][$i] > 5 * 1024 * 1024) {
            header('Location: editar_produto.php?id=' . $id . '&erro=' . urlencode('Uma das imagens é inválida ou ultrapassa 5 MB.'));
            exit;
        }
        $mime = $finfo->file($arquivos['tmp_name'][$i]);
        if (!isset($permitidos[$mime])) {
            header('Location: editar_produto.php?id=' . $id . '&erro=' . urlencode('Formato de imagem não permitido.'));
            exit;
        }
        $imagensValidas[] = ['tmp' => $arquivos['tmp_name'][$i], 'ext' => $permitidos[$mime]];
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE produtos SET nome=?, descricao=?, categoria=?, preco=?, preco_promocional=?, estoque=?, sku=?, status=? WHERE id=?");
    $stmt->execute([$nome, $descricao, $categoria, $preco, $preco_promocional, $estoque, $sku ?: null, $status, $id]);

    $pasta = __DIR__ . '/uploads/produtos';
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);

    foreach ($imagensValidas as $img) {
        $nomeArquivo = bin2hex(random_bytes(12)) . '.' . $img['ext'];
        if (!move_uploaded_file($img['tmp'], $pasta . '/' . $nomeArquivo)) {
            throw new RuntimeException('Falha ao salvar uma imagem.');
        }
        $caminho = 'uploads/produtos/' . $nomeArquivo;
        $stmtImg = $pdo->prepare("INSERT INTO produto_imagens (produto_id, imagem) VALUES (?, ?)");
        $stmtImg->execute([$id, $caminho]);
    }

    $stmt = $pdo->prepare("SELECT imagem_principal FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $principal = $stmt->fetchColumn();

    if (!$principal) {
        $stmt = $pdo->prepare("SELECT imagem FROM produto_imagens WHERE produto_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$id]);
        $novaPrincipal = $stmt->fetchColumn();
        if ($novaPrincipal) {
            $stmt = $pdo->prepare("UPDATE produtos SET imagem_principal = ? WHERE id = ?");
            $stmt->execute([$novaPrincipal, $id]);
        }
    }

    $pdo->commit();
    header('Location: produtos.php?mensagem=' . urlencode('Produto atualizado com sucesso.'));
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: editar_produto.php?id=' . $id . '&erro=' . urlencode('Não foi possível atualizar o produto.'));
    exit;
}
?>