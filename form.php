<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host     = 'localhost';
$dbname   = 'gestao_epi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

$tabela = $_GET['tabela'] ?? '';
$id     = (int)($_GET['id'] ?? 0);
$dados  = [];

if ($id > 0 && !empty($tabela)) {
    $stmt = $pdo->prepare("SELECT * FROM $tabela WHERE id = ?");
    $stmt->execute([$id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listas auxiliares para o formulário de empréstimos
$colaboradores = $pdo->query("SELECT id, nome FROM colaboradores WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$epis          = $pdo->query("SELECT id, nome, quantidade_estoque FROM epis ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$usuarios      = $pdo->query("SELECT id, nome FROM usuarios_sistema ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? 'Editar' : 'Cadastrar' ?> - Gestão de EPIs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-5" style="max-width: 700px;">
        <div class="card shadow border-0">
            <div class="card-header card-header-primary text-white">
                <h4 class="mb-0"><?= $id > 0 ? 'Editar Registro' : 'Novo Cadastro' ?> (<?= ucfirst($tabela) ?>)</h4>
            </div>
            <div class="card-body p-4">

                <form action="acoes.php" method="POST">
                    <input type="hidden" name="tabela" value="<?= htmlspecialchars($tabela) ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <!-- FORMULÁRIO DE EPIS -->
                    <?php if ($tabela === 'epis'): ?>
                        <div class="mb-3">
                            <label class="form-label">Nome do EPI</label>
                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Certificado de Aprovação (CA)</label>
                            <input type="text" name="CA" class="form-control" value="<?= htmlspecialchars($dados['CA'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantidade em Estoque</label>
                            <input type="number" name="quantidade_estoque" class="form-control" value="<?= $dados['quantidade_estoque'] ?? 0 ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Validade do CA</label>
                            <input type="date" name="validade_ca" class="form-control" value="<?= $dados['validade_ca'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($dados['descricao'] ?? '') ?></textarea>
                        </div>

                    <!-- FORMULÁRIO DE COLABORADORES -->
                    <?php elseif ($tabela === 'colaboradores'): ?>
                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($dados['cpf'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($dados['cargo'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Setor</label>
                            <input type="text" name="setor" class="form-control" value="<?= htmlspecialchars($dados['setor'] ?? '') ?>">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="ativo" class="form-check-input" id="ativoCheck" <?= ($dados['ativo'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ativoCheck">Colaborador Ativo</label>
                        </div>

                    <!-- FORMULÁRIO DE USUÁRIOS DO SISTEMA -->
                    <?php elseif ($tabela === 'usuarios_sistema'): ?>
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($dados['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha <?= $id > 0 ? '(Deixe em branco para não alterar)' : '' ?></label>
                            <input type="password" name="senha" class="form-control" <?= $id == 0 ? 'required' : '' ?>>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível de Acesso</label>
                            <select name="nivel_acesso" class="form-select">
                                <option value="operador" <?= ($dados['nivel_acesso'] ?? '') === 'operador' ? 'selected' : '' ?>>Operador</option>
                                <option value="admin" <?= ($dados['nivel_acesso'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                            </select>
                        </div>

                    <!-- FORMULÁRIO DE EMPRÉSTIMOS -->
                    <?php elseif ($tabela === 'emprestimos'): ?>
                        <div class="mb-3">
                            <label class="form-label">Colaborador</label>
                            <select name="colaborador_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($colaboradores as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($dados['colaborador_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">EPI</label>
                            <select name="epi_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($epis as $ep): ?>
                                    <option value="<?= $ep['id'] ?>" <?= ($dados['epi_id'] ?? '') == $ep['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ep['nome']) ?> (Estoque: <?= $ep['quantidade_estoque'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuário Responsável (Atendente)</label>
                            <select name="usuario_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($dados['usuario_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantidade" class="form-control" value="<?= $dados['quantidade'] ?? 1 ?>" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data Dev. Prevista</label>
                            <input type="date" name="data_devolucao_estimada" class="form-control" value="<?= $dados['data_devolucao_estimada'] ?? '' ?>">
                        </div>
                        <?php if ($id > 0): ?>
                            <div class="mb-3">
                                <label class="form-label">Data Dev. Real (Preencha ao devolver)</label>
                                <input type="datetime-local" name="data_devolucao_real" class="form-control" value="<?= $dados['data_devolucao_real'] ? date('Y-m-d\TH:i', strtotime($dados['data_devolucao_real'])) : '' ?>">
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Emprestado" <?= ($dados['status'] ?? '') === 'Emprestado' ? 'selected' : '' ?>>Emprestado</option>
                                <option value="Devolvido" <?= ($dados['status'] ?? '') === 'Devolvido' ? 'selected' : '' ?>>Devolvido</option>
                                <option value="Vencido/Danificado" <?= ($dados['status'] ?? '') === 'Vencido/Danificado' ? 'selected' : '' ?>>Vencido/Danificado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observação</label>
                            <textarea name="observacao" class="form-control" rows="2"><?= htmlspecialchars($dados['observacao'] ?? '') ?></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-custom">Salvar Registro</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>