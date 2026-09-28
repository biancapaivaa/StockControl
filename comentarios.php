<?php
require_once 'auth.php';
require_once 'conexao.php';

$produto_id = (int)($_GET['produto_id'] ?? 0);
$status_filtro = trim($_GET['status'] ?? '');

// Se produto_id foi definido, filtrar por esse produto
$sql = "SELECT c.*, p.nome as produto_nome 
        FROM comentarios c
        LEFT JOIN produtos p ON c.produto_id = p.id
        WHERE 1=1";
$params = [];

if ($produto_id > 0) {
    $sql .= " AND c.produto_id = ?";
    $params[] = $produto_id;
}

if ($status_filtro !== '') {
    $sql .= " AND c.status = ?";
    $params[] = $status_filtro;
}

$sql .= " ORDER BY c.criado_em DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comentarios = $stmt->fetchAll();

// Obter lista de produtos para filtro
$produtosStmt = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome");
$produtos = $produtosStmt->fetchAll();

$mensagem = $_GET['mensagem'] ?? '';
$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentários - Acessórios</title>
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
            <h1>Comentários dos Produtos</h1>
            <p>Visualize e modere os comentários dos clientes.</p>
        </div>
    </div>

    <?php if ($mensagem): ?><div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div><?php endif; ?>
    <?php if ($erro): ?><div class="alert alert-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

    <section class="panel">
        <form method="GET" class="filters">
            <div>
                <label for="produto_id">Produto</label>
                <select id="produto_id" name="produto_id">
                    <option value="">Todos</option>
                    <?php foreach ($produtos as $prod): ?>
                        <option value="<?= (int)$prod['id'] ?>" <?= $produto_id === (int)$prod['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="Pendente" <?= $status_filtro === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="Aprovado" <?= $status_filtro === 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="Rejeitado" <?= $status_filtro === 'Rejeitado' ? 'selected' : '' ?>>Rejeitado</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-dark" type="submit">Filtrar</button>
                <a href="comentarios.php" class="btn btn-light">Limpar</a>
            </div>
        </form>
    </section>

    <section class="panel comments-panel">
        <div class="panel-title">
            <h2>Comentários cadastrados</h2>
            <span><?= count($comentarios) ?> resultado(s)</span>
        </div>

        <?php if (!$comentarios): ?>
            <div class="empty-state">
                <h3>Nenhum comentário encontrado</h3>
                <p>Não há comentários de clientes no momento.</p>
            </div>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <div>
                                <strong><?= htmlspecialchars($comentario['nome']) ?></strong>
                                <span class="email"><?= htmlspecialchars($comentario['email']) ?></span>
                            </div>
                            <div class="comment-meta">
                                <span class="status status-<?= strtolower($comentario['status']) ?>">
                                    <?= htmlspecialchars($comentario['status']) ?>
                                </span>
                                <span class="date"><?= date('d/m/Y H:i', strtotime($comentario['criado_em'])) ?></span>
                            </div>
                        </div>
                        <div class="comment-body">
                            <strong><?= htmlspecialchars($comentario['produto_nome']) ?></strong>
                            <?php if ($comentario['avaliacao'] > 0): ?>
                                <div class="rating">
                                    <?php for ($i = 0; $i < $comentario['avaliacao']; $i++): ?>
                                        <span class="star filled">★</span>
                                    <?php endfor; ?>
                                    <?php for ($i = $comentario['avaliacao']; $i < 5; $i++): ?>
                                        <span class="star">★</span>
                                    <?php endfor; ?>
                                    <span class="rating-value"><?= (int)$comentario['avaliacao'] ?>/5</span>
                                </div>
                            <?php endif; ?>
                            <p><?= nl2br(htmlspecialchars($comentario['comentario'])) ?></p>
                        </div>
                        <div class="comment-actions">
                            <?php if ($comentario['status'] !== 'Aprovado'): ?>
                                <a href="aprovar_comentario.php?id=<?= (int)$comentario['id'] ?>" class="action-approve">Aprovar</a>
                            <?php endif; ?>
                            <?php if ($comentario['status'] !== 'Rejeitado'): ?>
                                <a href="rejeitar_comentario.php?id=<?= (int)$comentario['id'] ?>" class="action-reject">Rejeitar</a>
                            <?php endif; ?>
                            <a href="excluir_comentario.php?id=<?= (int)$comentario['id'] ?>" class="action-delete" data-confirm="Tem certeza que deseja excluir este comentário?">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<script src="js/script.js"></script>
</body>
</html>
