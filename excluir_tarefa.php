<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include_once 'conexao.php';

$mensagem = "";
$erro = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $erro = "ID da tarefa inválido.";
} else {
    $id = intval($_GET['id']);
    
    // Prepare e execute o DELETE
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $mensagem = "Tarefa excluída com sucesso!";
        } else {
            $erro = "Tarefa não encontrada ou já excluída.";
        }
    } else {
        $erro = "Erro ao excluir tarefa: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<title>Excluir Tarefa</title>
<style>
/* --- Cole aqui o CSS que você enviou --- */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

/* Reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Body */
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
  min-height: 100vh;
  color: #333;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 40px 20px;
}

/* Container */
.container {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(37, 117, 252, 0.3);
  max-width: 900px;
  width: 100%;
  padding: 40px 50px;
  animation: fadeInUp 0.6s ease forwards;
  text-align: center;
}

/* Header */
h1 {
  color: #2575fc;
  font-weight: 600;
  text-align: center;
  margin-bottom: 35px;
  letter-spacing: 1.2px;
}

/* Messages */
p.success {
  color: #2ecc71;
  font-weight: 600;
  margin-top: 20px;
  text-align: center;
  font-size: 18px;
}

p.error {
  color: #e74c3c;
  font-weight: 600;
  margin-top: 20px;
  text-align: center;
  font-size: 18px;
}

/* Buttons */
a.button {
  display: inline-block;
  margin-top: 30px;
  background: #2575fc;
  color: white;
  font-weight: 600;
  font-size: 18px;
  padding: 12px 30px;
  border-radius: 12px;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

a.button:hover {
  background-color: #6a11cb;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
</head>
<body>
<div class="container">
    <h1>Excluir Tarefa</h1>

    <?php if ($mensagem): ?>
        <p class="success"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p class="error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <a href="dashboard.php" class="button">Voltar para o Dashboard</a>
</div>
</body>
</html>
