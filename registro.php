<?php
session_start();
include_once 'conexao.php';

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Verifica se e-mail já existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $erro = "❌ Este e-mail já está cadastrado. Faça login.";
    } else {
        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $email, $senha);

        if ($stmt->execute()) {
            // Login automático após cadastro
            $_SESSION['usuario_id'] = $stmt->insert_id;
            $_SESSION['usuario_nome'] = $nome;
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = "❌ Erro: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Usuário</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 480px;
            margin: 60px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .top-bar a {
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            color: #2980b9;
            border-radius: 6px;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        .top-bar a:hover {
            background-color: #2980b9;
            color: white;
            border-color: #2980b9;
        }
        h1 {
            text-align: center;
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        input, button {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline-offset: 2px;
            outline-color: transparent;
            transition: outline-color 0.3s ease;
        }
        input:focus {
            outline-color: #2980b9;
            border-color: #2980b9;
        }
        button {
            background-color: #2980b9;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #1c5980;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .link-login {
            display: block;
            margin-top: 18px;
            text-align: center;
            font-weight: 600;
            color: #2980b9;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .link-login:hover {
            color: #1c5980;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <a href="login.php">&larr; Voltar</a>
    </div>

    <h1>Registrar Novo Usuário</h1>

    <form method="POST" novalidate>
        <input type="text" name="nome" placeholder="Nome completo" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
        <input type="email" name="email" placeholder="E-mail" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Registrar</button>
    </form>

    <a href="login.php" class="link-login">← Já tem conta? Fazer login</a>

    <?php if (!empty($erro)): ?>
        <p class="error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
</div>
</body>
</html>
