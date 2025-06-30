<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include_once 'conexao.php';

if (!isset($_GET['id'])) {
    echo "ID da tarefa não informado.";
    exit;
}

$tarefa_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// Busca tarefa
$sql = "SELECT * FROM tarefas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tarefa_id);
$stmt->execute();
$result = $stmt->get_result();
$tarefa = $result->fetch_assoc();

if (!$tarefa) {
    echo "Tarefa não encontrada.";
    exit;
}

// Busca usuários
$usuarios_result = $conn->query("SELECT id, nome FROM usuarios ORDER BY nome");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    $atribuida_para = $_POST['atribuida_para'] ?: null;
    $data_inicio = $_POST['data_inicio'] ?: null;
    $data_vencimento = $_POST['data_vencimento'] ?: null;

    $sqlUpdate = "UPDATE tarefas SET titulo = ?, descricao = ?, status = ?, atribuida_para = ?, data_inicio = ?, data_vencimento = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssssssi", $titulo, $descricao, $status, $atribuida_para, $data_inicio, $data_vencimento, $tarefa_id);

    if ($stmtUpdate->execute()) {
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Erro ao atualizar tarefa: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tarefa</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
    <h1>Editar Tarefa</h1>

    <?php if (!empty($erro)): ?>
        <p class="error"><?= $erro ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="titulo">Nome da atividade</label>
        <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($tarefa['titulo']) ?>" required>

        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao"><?= htmlspecialchars($tarefa['descricao']) ?></textarea>

        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="a_fazer" <?= $tarefa['status'] === 'a_fazer' ? 'selected' : '' ?>>A Fazer</option>
            <option value="em_andamento" <?= $tarefa['status'] === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
            <option value="concluida" <?= $tarefa['status'] === 'concluida' ? 'selected' : '' ?>>Concluída</option>
        </select>

        <label for="atribuida_para">Responsável</label>
        <select name="atribuida_para" id="atribuida_para">
            <option value="">-- Atribuir a --</option>
            <?php while ($usuario = $usuarios_result->fetch_assoc()): ?>
                <option value="<?= $usuario['id'] ?>" <?= $usuario['id'] == $tarefa['atribuida_para'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($usuario['nome']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="data_inicio">Data Inicial</label>
        <input type="date" name="data_inicio" id="data_inicio" value="<?= $tarefa['data_inicio'] ?>">

        <label for="data_vencimento">Data Final</label>
        <input type="date" name="data_vencimento" id="data_vencimento" value="<?= $tarefa['data_vencimento'] ?>">

        <button type="submit">Salvar Alterações</button>
    </form>
</div>
</body>
</html>
