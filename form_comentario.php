<?php
require_once 'conexao.php';

$produto_id = (int)($_GET['produto_id'] ?? 0);

if ($produto_id <= 0) {
    header("Location: produtos.php");
    exit;
}

// Obter informações do produto
$stmt = $pdo->prepare("SELECT id, nome, descricao, imagem_principal FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch();

if (!$produto) {
    header("Location: produtos.php");
    exit;
}

// Obter comentários aprovados do produto
$stmt = $pdo->prepare("SELECT * FROM comentarios WHERE produto_id = ? AND status = 'Aprovado' ORDER BY criado_em DESC");
$stmt->execute([$produto_id]);
$comentarios_aprovados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentários - <?= htmlspecialchars($produto['nome']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .comment-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .comment-form h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .rating-input {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }

        .rating-input .star {
            font-size: 24px;
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }

        .rating-input .star:hover,
        .rating-input .star.active {
            color: #ffc107;
        }

        .form-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .comments-section {
            margin-top: 40px;
        }

        .comments-section h3 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .comment-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .comment-author {
            font-weight: 600;
            color: #333;
        }

        .comment-date {
            font-size: 12px;
            color: #999;
        }

        .comment-rating {
            color: #ffc107;
            margin: 10px 0;
            font-size: 14px;
        }

        .comment-text {
            color: #555;
            line-height: 1.6;
        }

        .product-info {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .product-image {
            width: 100px;
            height: 100px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-details h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .product-details p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .product-info {
                flex-direction: column;
            }

            .form-group {
                margin-bottom: 12px;
            }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a href="produtos.php" class="brand brand-dark">
            <div class="brand-logo"><img src="assets/logo.png" alt="Logo da empresa"></div>
            <div>
                <strong>StockControl</strong>
                <span>Painel de produtos</span>
            </div>
        </a>
        <div class="user-area">
            <a class="btn btn-secondary" href="produtos.php">← Voltar</a>
        </div>
    </div>
</header>

<main class="page">
    <div class="product-info">
        <div class="product-image">
            <?php if ($produto['imagem_principal']): ?>
                <img src="<?= htmlspecialchars($produto['imagem_principal']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
            <?php else: ?>
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0f0f0;">
                    <span style="color: #999;">SEM IMAGEM</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="product-details">
            <h2><?= htmlspecialchars($produto['nome']) ?></h2>
            <p><?= htmlspecialchars(mb_strimwidth($produto['descricao'], 0, 150, '...')) ?></p>
        </div>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">Seu comentário foi enviado com sucesso e está aguardando aprovação!</div>
    <?php endif; ?>

    <div class="comment-form">
        <h3>Deixe seu comentário</h3>
        <form method="POST" action="salvar_comentario.php">
            <input type="hidden" name="produto_id" value="<?= (int)$produto_id ?>">

            <div class="form-group">
                <label for="nome">Nome*</label>
                <input type="text" id="nome" name="nome" required maxlength="120" placeholder="Seu nome completo">
            </div>

            <div class="form-group">
                <label for="email">E-mail*</label>
                <input type="email" id="email" name="email" required maxlength="180" placeholder="seu@email.com">
            </div>

            <div class="form-group">
                <label>Avaliação</label>
                <div class="rating-input" id="ratingInput">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" id="avaliacao" name="avaliacao" value="0">
            </div>

            <div class="form-group">
                <label for="comentario">Comentário*</label>
                <textarea id="comentario" name="comentario" required placeholder="Compartilhe sua opinião sobre este produto..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enviar Comentário</button>
                <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <?php if ($comentarios_aprovados): ?>
        <div class="comments-section">
            <h3>Comentários dos Clientes (<?= count($comentarios_aprovados) ?>)</h3>
            <?php foreach ($comentarios_aprovados as $comentario): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div>
                            <div class="comment-author"><?= htmlspecialchars($comentario['nome']) ?></div>
                            <div class="comment-date"><?= date('d/m/Y', strtotime($comentario['criado_em'])) ?></div>
                        </div>
                    </div>
                    <?php if ($comentario['avaliacao'] > 0): ?>
                        <div class="comment-rating">
                            <?php for ($i = 0; $i < $comentario['avaliacao']; $i++): ?>
                                <span>★</span>
                            <?php endfor; ?>
                            <?php for ($i = $comentario['avaliacao']; $i < 5; $i++): ?>
                                <span style="color: #ddd;">★</span>
                            <?php endfor; ?>
                            <span style="color: #666; margin-left: 5px;"><?= (int)$comentario['avaliacao'] ?>/5</span>
                        </div>
                    <?php endif; ?>
                    <div class="comment-text">
                        <?= nl2br(htmlspecialchars($comentario['comentario'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="comments-section">
            <h3>Comentários dos Clientes</h3>
            <div class="empty-state">
                <h4>Sem comentários aprovados</h4>
                <p>Seja o primeiro a comentar este produto!</p>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    document.querySelectorAll('#ratingInput .star').forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            document.getElementById('avaliacao').value = value;
            
            document.querySelectorAll('#ratingInput .star').forEach((s, index) => {
                if (index < value) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });

        star.addEventListener('mouseover', function() {
            const value = this.getAttribute('data-value');
            document.querySelectorAll('#ratingInput .star').forEach((s, index) => {
                if (index < value) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });

    document.getElementById('ratingInput').addEventListener('mouseleave', function() {
        const value = document.getElementById('avaliacao').value;
        document.querySelectorAll('#ratingInput .star').forEach((s, index) => {
            if (index < value) {
                s.style.color = '#ffc107';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
</script>
<script src="js/script.js"></script>
</body>
</html>
