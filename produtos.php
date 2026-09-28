<?php
require_once 'auth.php';
require_once 'conexao.php';

$busca = trim($_GET['busca'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT p.*,
        (SELECT imagem FROM produto_imagens pi WHERE pi.produto_id = p.id ORDER BY pi.id ASC LIMIT 1) AS imagem_extra,
        (SELECT COUNT(*) FROM comentarios c WHERE c.produto_id = p.id) AS total_comentarios,
        (SELECT COUNT(*) FROM comentarios c WHERE c.produto_id = p.id AND c.status = 'Pendente') AS comentarios_pendentes
        FROM produtos p WHERE 1=1";
$params = [];

if ($busca !== '') {
    $sql .= " AND (p.nome LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}
if ($categoria !== '') {
    $sql .= " AND p.categoria = ?";
    $params[] = $categoria;
}
if ($status !== '') {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

$categoriasStmt = $pdo->query("SELECT DISTINCT categoria FROM produtos WHERE categoria <> '' ORDER BY categoria");
$categorias = $categoriasStmt->fetchAll(PDO::FETCH_COLUMN);

$mensagem = $_GET['mensagem'] ?? '';
$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Acessórios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a href="produtos.php" class="brand brand-dark">
            <div class="brand-logo"><img src="assets/logo.png" alt="Logo da empresa"></div>
            <div>
                <strong>StockControl</strong>
                <span>Painel administrativo</span>
            </div>
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

<main class="page">
    <div class="page-header">
        <div>
            <span class="eyebrow">GERENCIAMENTO</span>
            <h1>Produtos</h1>
            <p>Cadastre, edite e organize os produtos da empresa.</p>
        </div>
        <div class="header-actions">
            <a href="comentarios.php" class="btn btn-secondary">Comentários</a>
            <a href="novo_produto.php" class="btn btn-primary">+ Adicionar Produto</a>
        </div>
    </div>

    <?php if ($mensagem): ?><div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert alert-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <section class="panel">
        <form method="GET" class="filters">
            <div class="filter-search">
                <label for="busca">Pesquisar</label>
                <input type="search" id="busca" name="busca" placeholder="Nome ou SKU..." value="<?= htmlspecialchars($busca) ?>">
            </div>
            <div>
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="Ativo" <?= $status === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="Inativo" <?= $status === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-dark" type="submit">Filtrar</button>
                <a href="produtos.php" class="btn btn-light">Limpar</a>
            </div>
        </form>
    </section>

    <section class="panel products-panel">
        <div class="panel-title">
            <h2>Produtos cadastrados</h2>
            <span><?= count($produtos) ?> resultado(s)</span>
        </div>

        <?php if (!$produtos): ?>
            <div class="empty-state">
                <div class="empty-icon">+</div>
                <h3>Nenhum produto encontrado</h3>
                <p>Cadastre o primeiro produto ou altere os filtros.</p>
                <a href="novo_produto.php" class="btn btn-primary">Adicionar produto</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>SKU</th>
                            <th>Status</th>
                            <th>Comentários</th>
                            <th>Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($produtos as $produto):
                        $imagem = $produto['imagem_principal'] ?: $produto['imagem_extra'];
                    ?>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <?php if ($imagem): ?>
                                        <img src="<?= htmlspecialchars($imagem) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder">IMG</div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                        <small><?= htmlspecialchars(mb_strimwidth($produto['descricao'], 0, 55, '...')) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($produto['categoria']) ?></td>
                            <td>
                                <strong>R$ <?= number_format((float)$produto['preco'], 2, ',', '.') ?></strong>
                                <?php if ((float)$produto['preco_promocional'] > 0): ?>
                                    <small class="promo-price">Promo: R$ <?= number_format((float)$produto['preco_promocional'], 2, ',', '.') ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$produto['estoque'] ?></td>
                            <td><?= htmlspecialchars($produto['sku'] ?: '-') ?></td>
                            <td><span class="status <?= $produto['status'] === 'Ativo' ? 'status-active' : 'status-inactive' ?>"><?= htmlspecialchars($produto['status']) ?></span></td>
                            <td>
                                <a href="comentarios.php?produto_id=<?= (int)$produto['id'] ?>" class="comments-link">
                                    <?= (int)$produto['total_comentarios'] ?> comentário(s)
                                    <?php if ((int)$produto['comentarios_pendentes'] > 0): ?>
                                        <span class="badge-pending"><?= (int)$produto['comentarios_pendentes'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($produto['criado_em'])) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="editar_produto.php?id=<?= (int)$produto['id'] ?>" class="action-edit">Editar</a>
                                    <a href="form_comentario.php?produto_id=<?= (int)$produto['id'] ?>" class="action-comments">Comentários</a>
                                    <a href="excluir_produto.php?id=<?= (int)$produto['id'] ?>" class="action-delete" data-confirm="Tem certeza que deseja excluir este produto?">Excluir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<script src="js/script.js"></script>
</body>
</html>