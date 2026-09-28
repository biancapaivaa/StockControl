<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: produtos.php');
    exit;
}

$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta - StockControl</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <main class="auth-container">
        <section class="auth-card auth-card-wide">
            <div class="brand">
                <div class="brand-logo"><img src="assets/logo.png" alt="Logo da empresa"></div>
                <div>
                    <strong>StockControl</strong>
                    <span>Painel administrativo</span>
                </div>
            </div>

            <div class="auth-heading">
                <h1>Criar conta</h1>
                <p>Cadastre um usuário para acessar o sistema.</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="salvar_usuario.php" method="POST" class="form">
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" required maxlength="120" autocomplete="name">

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required maxlength="180" autocomplete="email">

                <label for="senha">Senha</label>
                <div class="password-field">
                    <input type="password" id="senha" name="senha" required minlength="6" autocomplete="new-password">
                    <button type="button" class="toggle-password" data-target="senha">Mostrar</button>
                </div>

                <label for="confirmar_senha">Confirmar senha</label>
                <div class="password-field">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6" autocomplete="new-password">
                    <button type="button" class="toggle-password" data-target="confirmar_senha">Mostrar</button>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Criar conta</button>
            </form>

            <p class="auth-footer">Já possui conta? <a href="login.php">Voltar para o login</a></p>
        </section>
    </main>
    <script src="js/script.js"></script>
</body>
</html>