<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
include_once 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

// Filtro por usuário atribuído (via GET)
$filtro_usuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : 0;

// Busca lista de usuários
$usuarios_result = $conn->query("SELECT id, nome FROM usuarios ORDER BY nome");

// Busca tarefas por status
$tarefasPorStatus = [
    'a_fazer' => [],
    'em_andamento' => [],
    'concluida' => []
];

$sql = "SELECT t.id, t.titulo, t.data_inicio, t.data_vencimento, t.data_termino, t.status, u.nome AS atribuido_para
        FROM tarefas t
        LEFT JOIN usuarios u ON t.atribuida_para = u.id";

if ($filtro_usuario > 0) {
    $sql .= " WHERE t.atribuida_para = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $filtro_usuario);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

while ($tarefa = $result->fetch_assoc()) {
    $status = $tarefa['status'] ?? 'a_fazer';
    $tarefasPorStatus[$status][] = $tarefa;
}

function formatDate($date) {
    if (!$date || $date === '0000-00-00') return '-';
    $d = new DateTime($date);
    return $d->format('d/m/Y');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Tarefas</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1300px;
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
            margin-bottom: 15px;
        }
        .logout-link {
            background-color: #e74c3c;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        h1 {
            text-align: center;
            margin: 10px 0;
        }
        .filter {
            text-align: center;
            margin-bottom: 20px;
        }
        .create-task-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 16px;
            background-color: #2ecc71;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .kanban-board {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }
        .kanban-column {
            flex: 1;
            background: #f1f1f1;
            padding: 15px;
            border-radius: 8px;
        }
        .kanban-column h2 {
            text-align: center;
        }
        .kanban-task {
            background: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .kanban-task p {
            margin: 5px 0;
        }
        .kanban-task-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .kanban-task-actions a {
            font-size: 0.9em;
            text-decoration: none;
            color: #3498db;
            margin-right: 8px;
        }
        .kanban-task-actions a.excluir {
            color: #e74c3c;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <a href="nova_tarefa.php" class="create-task-link">+ Criar Nova Tarefa</a>
        <a href="login.php" class="logout-link">Sair</a>
    </div>

    <h1>Dashboard de Tarefas</h1>

    <div class="filter">
        <form method="GET" action="dashboard.php">
            <label for="usuario">Filtrar por usuário atribuído:</label>
            <select name="usuario" id="usuario" onchange="this.form.submit()">
                <option value="0">Todos</option>
                <?php while ($usuario = $usuarios_result->fetch_assoc()): ?>
                    <option value="<?= $usuario['id'] ?>" <?= ($usuario['id'] == $filtro_usuario) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <div class="kanban-board">
        <?php
        $statusLabels = [
            'a_fazer' => 'A Fazer',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída'
        ];
        foreach ($statusLabels as $statusKey => $statusLabel):
        ?>
            <div class="kanban-column">
                <h2><?= $statusLabel ?></h2>
                <?php foreach ($tarefasPorStatus[$statusKey] as $tarefa): ?>
                    <div class="kanban-task">
                        <h3><?= htmlspecialchars($tarefa['titulo']) ?></h3>
                        <p><strong>Início:</strong> <?= formatDate($tarefa['data_inicio']) ?></p>
                        <p><strong>Vencimento:</strong> <?= formatDate($tarefa['data_vencimento']) ?></p>
                        <p><strong>Término:</strong> <?= formatDate($tarefa['data_termino']) ?></p>
                        <p><strong>Atribuído para:</strong> <?= htmlspecialchars($tarefa['atribuido_para'] ?? '-') ?></p>
                        <div class="kanban-task-actions">
                            <a href="editar_tarefa.php?id=<?= $tarefa['id'] ?>">Editar</a>
                            <a href="excluir_tarefa.php?id=<?= $tarefa['id'] ?>" class="excluir" onclick="return confirm('Tem certeza que deseja excluir esta tarefa?')">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
