<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include_once 'conexao.php';

$usuarios_result = $conn->query("SELECT id, nome FROM usuarios ORDER BY nome");

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $status = $_POST['status'] ?? 'a_fazer';
    $atribuida_para = $_POST['atribuida_para'] ?: null;
    $data_inicio = $_POST['data_inicio'] ?: null;
    $data_vencimento = $_POST['data_vencimento'] ?: null;

    if (!$titulo) {
        $erro = "O título é obrigatório.";
    } else {
        // Ajuste aqui: não insira projeto_id (removido)
        $sqlInsert = "INSERT INTO tarefas (titulo, descricao, status, atribuida_para, data_inicio, data_vencimento) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("ssssss", $titulo, $descricao, $status, $atribuida_para, $data_inicio, $data_vencimento);

        if ($stmtInsert->execute()) {
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = "Erro ao criar tarefa: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Criar Nova Tarefa</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Seu CSS permanece igual */
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
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
        }
        .top-bar .logout-link {
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
        }
        h1 {
            text-align: center;
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        label {
            font-weight: bold;
            margin-bottom: 4px;
        }
        input, textarea, select, button {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background-color: #2980b9;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #1c5980;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <a href="dashboard.php">&larr; Voltar</a>
        <a href="login.php" class="logout-link">Sair</a>
    </div>

    <h1>Criar Nova Tarefa</h1>

    <?php if ($erro): ?>
        <p class="error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="titulo">Nome da atividade</label>
        <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" required>

        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao"><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>

        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="a_fazer" <?= (($_POST['status'] ?? '') === 'a_fazer') ? 'selected' : '' ?>>A Fazer</option>
            <option value="em_andamento" <?= (($_POST['status'] ?? '') === 'em_andamento') ? 'selected' : '' ?>>Em Andamento</option>
            <option value="concluida" <?= (($_POST['status'] ?? '') === 'concluida') ? 'selected' : '' ?>>Concluída</option>
        </select>

        <label for="atribuida_para">Responsável</label>
        <select name="atribuida_para" id="atribuida_para">
            <option value="">-- Atribuir a --</option>
            <?php while ($usuario = $usuarios_result->fetch_assoc()): ?>
                <option value="<?= $usuario['id'] ?>" <?= (($_POST['atribuida_para'] ?? '') == $usuario['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($usuario['nome']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="data_inicio">Data Inicial</label>
        <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>">

        <label for="data_vencimento">Data Final</label>
        <input type="date" name="data_vencimento" id="data_vencimento" value="<?= htmlspecialchars($_POST['data_vencimento'] ?? '') ?>">

        <button type="submit">Criar Tarefa</button>
    </form>
</div>
</body>
</html>
