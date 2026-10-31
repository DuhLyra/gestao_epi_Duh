<?php
session_start();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $nivel = 'operador';

    $host     = 'localhost';
    $dbname   = 'gestao_epi';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica se o e-mail já existe na base de dados
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios_sistema WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->rowCount() > 0) {
            $erro = "Este e-mail já está registado no sistema.";
        } else {
            // Insere o novo utilizador com senha encriptada
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuarios_sistema (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senhaHash, $nivel]);

            header("Location: login.php?alerta=cadastro_sucesso");
            exit;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Gestão de EPIs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-lg p-4 border-0" style="width: 100%; max-width: 400px; background-color: var(--cor-card-bg);">
        <div class="card-body">
            <h3 class="text-center mb-4 fw-bold text-white">Criar Nova Conta</h3>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form action="cadastro.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light">Nome Completo</label>
                    <input type="text" name="nome" class="form-control bg-dark text-light border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-light">E-mail</label>
                    <input type="email" name="email" class="form-control bg-dark text-light border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-light">Palavra-passe</label>
                    <input type="password" name="senha" class="form-control bg-dark text-light border-secondary" required>
                </div>
                <button type="submit" class="btn btn-custom w-100 py-2 fw-bold mb-3 text-white" style="background-color: var(--cor-primaria);">Concluir Cadastro</button>
            </form>

            <div class="text-center">
                <a href="login.php" class="text-decoration-none small text-muted">Voltar para o Log-in</a>
            </div>
        </div>
    </div>
</body>
</html>