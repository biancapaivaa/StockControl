<?php
$isEdit = isset($produto);
$titulo = $isEdit ? 'Editar produto' : 'Adicionar produto';
$acao = $isEdit ? 'editar_produto.php?id=' . (int)$produto['id'] : 'salvar_produto.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> - Acessórios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a href="produtos.php" class="brand brand-dark">
            <div class="brand-logo"><img src="assets/logo.png" alt="Logo da empresa"></div>
            <div><strong>Acessórios</strong><span>Painel administrativo</span></div>
        </a>
        <div class="user-area">
            <div class="user-info">
                <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
                <span><?= htmlspecialchars($_SESSION['usuario_email']) ?></span>
            </div>
            <a class="btn btn-outline" href="logout.php">Sair</a>
        </div>
    </div>
</header>

<main class="page page-form">
    <div class="page-header">
        <div>
            <a href="produtos.php" class="back-link">← Voltar para produtos</a>
            <span class="eyebrow"><?= $isEdit ? 'PRODUTO' : 'NOVO PRODUTO' ?></span>
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p>Preencha os dados do produto abaixo.</p>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($acao) ?>" method="POST" enctype="multipart/form-data" class="product-form">
        <section class="panel">
            <div class="panel-title"><h2>Informações do produto</h2></div>
            <div class="form-grid">
                <div class="field full">
                    <label for="nome">Nome do produto *</label>
                    <input type="text" id="nome" name="nome" maxlength="150" required value="<?= htmlspecialchars($produto['nome'] ?? '') ?>">
                </div>

                <div class="field full">
                    <label for="descricao">Descrição *</label>
                    <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label for="categoria">Categoria *</label>
                    <input type="text" id="categoria" name="categoria" maxlength="100" required placeholder="Ex.: Acessórios" value="<?= htmlspecialchars($produto['categoria'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="sku">SKU</label>
                    <input type="text" id="sku" name="sku" maxlength="80" value="<?= htmlspecialchars($produto['sku'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="preco">Preço *</label>
                    <input type="number" id="preco" name="preco" min="0" step="0.01" required value="<?= htmlspecialchars($produto['preco'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="preco_promocional">Preço promocional</label>
                    <input type="number" id="preco_promocional" name="preco_promocional" min="0" step="0.01" value="<?= htmlspecialchars($produto['preco_promocional'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="estoque">Estoque *</label>
                    <input type="number" id="estoque" name="estoque" min="0" step="1" required value="<?= htmlspecialchars($produto['estoque'] ?? '0') ?>">
                </div>

                <div class="field">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Ativo" <?= ($produto['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= ($produto['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-title">
                <div>
                    <h2>Imagens</h2>
                    <p class="panel-subtitle">JPG, JPEG, PNG ou WEBP. Até 5 MB por arquivo.</p>
                </div>
            </div>

            <?php if ($isEdit && !empty($imagens)): ?>
                <div class="existing-images">
                    <?php foreach ($imagens as $imagem): ?>
                        <div class="existing-image">
                            <img src="<?= htmlspecialchars($imagem['imagem']) ?>" alt="Imagem do produto">
                            <?php if ($imagem['imagem'] === ($produto['imagem_principal'] ?? '')): ?>
                                <span>Principal</span>
                            <?php endif; ?>
                            <a href="excluir_imagem.php?id=<?= (int)$imagem['id'] ?>&produto_id=<?= (int)$produto['id'] ?>" class="remove-image" data-confirm="Remover esta imagem?">Remover</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="upload-box">
                <label for="imagens">Selecionar imagens</label>
                <input type="file" id="imagens" name="imagens[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
                <p>Você pode selecionar várias imagens. A primeira imagem enviada será usada como principal quando o produto ainda não possuir uma.</p>
            </div>

            <div id="preview" class="preview-grid"></div>
        </section>

        <div class="form-footer">
            <a href="produtos.php" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
        </div>
    </form>
</main>

<script src="js/script.js"></script>
</body>
</html>
