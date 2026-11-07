<?php
session_start();

// Inclui a conexão centralizada com as variáveis de ambiente do Aiven
require_once 'conexao.php';

// Caso haja processamento POST do formulário no próprio arquivo:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        try {
            // Exemplo de inserção utilizando o $pdo vindo do conexao.php
            $stmt = $pdo->prepare("INSERT INTO usuarios_sistema (nome, email, senha) VALUES (?, ?, ?)");
            // Recomenda-se password_hash($senha, PASSWORD_DEFAULT) para maior segurança
            $stmt->execute([$nome, $email, $senha]); 

            header("Location: login.php?alerta=cadastro_sucesso");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar usuário: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>