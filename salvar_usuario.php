<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar_senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '' || $confirmar === '') {
    header('Location: cadastro.php?erro=' . urlencode('Preencha todos os campos.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cadastro.php?erro=' . urlencode('Informe um e-mail válido.'));
    exit;
}

if (strlen($senha) < 6) {
    header('Location: cadastro.php?erro=' . urlencode('A senha deve ter pelo menos 6 caracteres.'));
    exit;
}

if ($senha !== $confirmar) {
    header('Location: cadastro.php?erro=' . urlencode('As senhas não coincidem.'));
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    header('Location: cadastro.php?erro=' . urlencode('Este e-mail já está cadastrado.'));
    exit;
}

$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
$stmt->execute([$nome, $email, $hash]);

header('Location: login.php?sucesso=' . urlencode('Conta criada com sucesso. Faça login.'));
exit;
?>