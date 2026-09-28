<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: produtos.php');
    exit;
}

$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Acessórios</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <main class="auth-container">
        <section class="auth-card">
            <div class="brand">
                <div class="brand-logo"><img src="assets/logo.png" alt="Logo da empresa"></div>
                <div>
                    <strong>Acessórios</strong>
                    <span>Painel administrativo</span>
                </div>
            </div>

            <div class="auth-heading">
                <h1>Entrar</h1>
                <p>Acesse o gerenciamento de produtos.</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form action="autenticar.php" method="POST" class="form">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required autocomplete="email">

                <label for="senha">Senha</label>
                <div class="password-field">
                    <input type="password" id="senha" name="senha" required autocomplete="current-password">
                    <button type="button" class="toggle-password" data-target="senha">Mostrar</button>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Entrar</button>
            </form>

            <p class="auth-footer">Ainda não possui conta? <a href="cadastro.php">Criar conta</a></p>
        </section>
    </main>
    <script src="js/script.js"></script>
</body>
</html>