<?php
session_start();
$erro = '';
$sucesso = '';

if (isset($_GET['alerta']) && $_GET['alerta'] === 'cadastro_sucesso') {
    $sucesso = "Conta criada com sucesso! Faça login para entrar.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $host     = 'localhost';
    $dbname   = 'gestao_epi';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Valida os dados existentes na base de dados
        $stmt = $pdo->prepare("SELECT * FROM usuarios_sistema WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];

            header("Location: index.php?alerta=login_sucesso");
            exit;
        } else {
            $erro = "E-mail ou palavra-passe incorretos.";
        }
    } catch (PDOException $e) {
        $erro = "Erro na base de dados: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Entrar no Sistema - Gestão de EPIs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-lg p-4 border-0" style="width: 100%; max-width: 400px; background-color: var(--cor-card-bg);">
        <div class="card-body">
            <h3 class="text-center mb-4 fw-bold text-white">Entrar no Portal</h3>
            
            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success py-2 small"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light">E-mail</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-light">Palavra-passe</label>
                    <input type="password" name="senha" class="form-control bg-dark text-light border-secondary" required>
                </div>
                
                <!-- Botão de Validação e Entrada -->
                <button type="submit" class="btn btn-custom w-100 py-2 fw-bold mb-3">Entrar</button>
            </form>

            <!-- Botão Direcionando para o Cadastro (Abaixo do botão Entrar) -->
            <div class="d-grid gap-2 mb-3">
                <a href="cadastro.php" class="btn btn-outline-secondary py-2 fw-bold">Não tem conta? Cadastre-se</a>
            </div>

            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none small text-muted">← Voltar para a Página Inicial</a>
            </div>
        </div>
    </div>
</body>
</html>