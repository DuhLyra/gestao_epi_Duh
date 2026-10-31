<?php
session_start();

$host     = 'localhost';
$dbname   = 'gestao_epi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("Location: index.php?erro=conexao");
    exit;
}

$acao = $_REQUEST['acao'] ?? '';
$tabela = $_REQUEST['tabela'] ?? '';

try {
    // Processamento da exclusão de dados sem exigência de login
    if ($acao === 'excluir') {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id > 0 && in_array($tabela, ['epis', 'colaboradores', 'emprestimos'])) {
            if ($tabela === 'epis') {
                $stmtDelEmp = $pdo->prepare("DELETE FROM emprestimos WHERE epi_id = ?");
                $stmtDelEmp->execute([$id]);
            } elseif ($tabela === 'colaboradores') {
                $stmtDelEmp = $pdo->prepare("DELETE FROM emprestimos WHERE colaborador_id = ?");
                $stmtDelEmp->execute([$id]);
            }

            $stmt = $pdo->prepare("DELETE FROM $tabela WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header("Location: index.php?alerta=excluido_com_sucesso");
        exit;
    }

    // Ações de Inserção e Atualização via POST sem exigência de login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)($_POST['id'] ?? 0);

        if ($tabela === 'epis') {
            $nome = $_POST['nome'] ?? '';
            $ca = $_POST['CA'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $estoque = (int)($_POST['quantidade_estoque'] ?? 0);
            $validade = !empty($_POST['validade_ca']) ? $_POST['validade_ca'] : null;

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE epis SET nome = ?, CA = ?, descricao = ?, quantidade_estoque = ?, validade_ca = ? WHERE id = ?");
                $stmt->execute([$nome, $ca, $descricao, $estoque, $validade, $id]);
                $msg = "epi_atualizado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO epis (nome, CA, descricao, quantidade_estoque, validade_ca) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $ca, $descricao, $estoque, $validade]);
                $msg = "epi_adicionada";
            }
        } elseif ($tabela === 'colaboradores') {
            $nome = $_POST['nome'] ?? '';
            $cpf = $_POST['cpf'] ?? '';
            $cargo = $_POST['cargo'] ?? '';
            $setor = $_POST['setor'] ?? '';
            $ativo = isset($_POST['ativo']) ? 1 : 0;

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE colaboradores SET nome = ?, cpf = ?, cargo = ?, setor = ?, ativo = ? WHERE id = ?");
                $stmt->execute([$nome, $cpf, $cargo, $setor, $ativo, $id]);
                $msg = "colaborador_atualizado";
            } else {
                $stmt = $pdo->prepare("INSERT INTO colaboradores (nome, cpf, cargo, setor, ativo) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $cpf, $cargo, $setor, $ativo]);
                $msg = "colaborador_adicionado";
            }
        } elseif ($tabela === 'emprestimos') {
            $colaborador_id = (int)($_POST['colaborador_id'] ?? 0);
            $epi_id = (int)($_POST['epi_id'] ?? 0);
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $estimada = !empty($_POST['data_devolucao_estimada']) ? $_POST['data_devolucao_estimada'] : null;
            $status = $_POST['status'] ?? 'Ativo';
            $observacao = $_POST['observacao'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO emprestimos (colaborador_id, epi_id, usuario_id, quantidade, data_devolucao_estimada, status, observacao) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$colaborador_id, $epi_id, $usuario_id, $quantidade, $estimada, $status, $observacao]);
            
            $stmtBaixa = $pdo->prepare("UPDATE epis SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?");
            $stmtBaixa->execute([$quantidade, $epi_id]);

            $msg = "emprestimo_registrado";
        }

        header("Location: index.php?alerta=" . ($msg ?? 'sucesso'));
        exit;
    }
} catch (Exception $e) {
    header("Location: index.php?erro=falha_operacao");
    exit;
}
?>