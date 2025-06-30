<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
include_once 'conexao.php';

$sql = "SELECT * FROM projetos ORDER BY criado_em DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Projetos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Projetos</h1>
    <p>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?> | <a href="logout.php">Sair</a></p>
    <a href="criar_projeto.php">+ Novo Projeto</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Criado por</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['titulo']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['descricao'])) ?></td>

                        <?php
                        // Buscar nome do criador
                        $criadorId = $row['criado_por'];
                        $stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
                        $stmt->bind_param("i", $criadorId);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $criador = $res->fetch_assoc();
                        ?>

                        <td><?= htmlspecialchars($criador['nome'] ?? 'Desconhecido') ?></td>
                        <td><?= $row['criado_em'] ?></td>
                        <td>
                            <a href="tarefas.php?projeto_id=<?= $row['id'] ?>">Ver Tarefas</a> |
                            <a href="editar_projeto.php?id=<?= $row['id'] ?>">Editar</a> |
                            <a href="excluir_projeto.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Nenhum projeto encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
  </div>
</body>
</html>
