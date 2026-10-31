<?php
session_start();

// Impede o navegador de guardar em cache as páginas protegidas
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$logado = isset($_SESSION['usuario_id']);

// Inclui o ficheiro de conexão com a base de dados
require_once 'conexao.php';

try {
    $epis = $pdo->query("SELECT * FROM epis ORDER BY nome ASC")->fetchAll();
    $colaboradores = $pdo->query("SELECT * FROM colaboradores ORDER BY nome ASC")->fetchAll();
    
    $sqlEmprestimos = "
        SELECT e.id, c.nome AS colaborador_nome, ep.nome AS epi_nome, u.nome AS usuario_nome,
               e.quantidade, e.data_emprestimo, e.data_devolucao_estimada, e.status
        FROM emprestimos e
        JOIN colaboradores c ON e.colaborador_id = c.id
        JOIN epis ep ON e.epi_id = ep.id
        JOIN usuarios_sistema u ON e.usuario_id = u.id
        ORDER BY e.data_emprestimo DESC
    ";
    $emprestimos = $pdo->query($sqlEmprestimos)->fetchAll();
} catch (PDOException $e) {
    $epis = $colaboradores = $emprestimos = [];
}
?>

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $epis = $pdo->query("SELECT * FROM epis ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    $colaboradores = $pdo->query("SELECT * FROM colaboradores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    
    $sqlEmprestimos = "
        SELECT e.id, c.nome AS colaborador_nome, ep.nome AS epi_nome, u.nome AS usuario_nome,
               e.quantidade, e.data_emprestimo, e.data_devolucao_estimada, e.status
        FROM emprestimos e
        JOIN colaboradores c ON e.colaborador_id = c.id
        JOIN epis ep ON e.epi_id = ep.id
        JOIN usuarios_sistema u ON e.usuario_id = u.id
        ORDER BY e.data_emprestimo DESC
    ";
    $emprestimos = $pdo->query($sqlEmprestimos)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $epis = $colaboradores = $emprestimos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gestão de EPIs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; min-height: 100vh; overflow-x: hidden; }
        #sidebar {
            width: 260px;
            background-color: var(--cor-card-bg);
            border-right: 1px solid var(--cor-borda);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
        }
        #content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 30px;
        }
        .sidebar-nav { flex-grow: 1; padding: 20px; }
        .sidebar-nav a {
            display: block;
            color: var(--cor-texto);
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }
        .sidebar-nav a:hover {
            background-color: var(--cor-primaria);
            color: #fff;
        }
        .user-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--cor-borda);
            background-color: rgba(0,0,0,0.2);
            cursor: pointer;
            user-select: none;
        }
        .user-footer:hover { background-color: rgba(140,115,101,0.1); }
    </style>
</head>
<body>

    <!-- MENU LATERAL -->
    <nav id="sidebar">
        <div class="p-4 border-bottom border-secondary">
            <h5 class="text-white fw-bold m-0">Gestão de EPIs</h5>
        </div>
        <div class="sidebar-nav">
            <span class="text-muted small text-uppercase fw-bold px-3 mb-2 d-block">Tópicos</span>
            <a href="#sobre">Sobre a Empresa</a>
            <a href="#emprestimos">Registro de Empréstimos</a>
            <a href="#epis">Equipamentos (EPIs)</a>
            <a href="#colaboradores">Colaboradores</a>
            
            <div class="mt-4 pt-3 border-top border-secondary">
                <?php if (!$logado): ?>
                    <a href="login.php" class="btn btn-custom w-100 text-center fw-bold py-2 text-white mb-2">Entrar</a>
                    <a href="cadastro.php" class="btn btn-outline-secondary w-100 text-center fw-bold py-2">Cadastrar-se</a>
                <?php else: ?>
                    <a href="logout.php" class="btn btn-outline-danger w-100 text-center fw-bold py-2">Terminar Sessão</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ÁREA INFERIOR DO UTILIZADOR -->
        <div class="user-footer d-flex align-items-center gap-3" id="userArea" title="Clique com o botão direito para editar as suas informações">
            <img src="https://ui-avatars.com/api/?name=<?= $logado ? urlencode($_SESSION['usuario_nome']) : 'Visitante' ?>&background=593d31&color=fff" class="rounded-circle" width="40" height="40" alt="Foto">
            <div class="overflow-hidden">
                <h6 class="text-white mb-0 text-truncate" style="font-size: 0.9rem;"><?= $logado ? htmlspecialchars($_SESSION['usuario_nome']) : 'Visitante' ?></h6>
                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;"><?= $logado ? htmlspecialchars($_SESSION['usuario_email']) : 'Clique direito para editar' ?></small>
            </div>
        </div>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="content">

        <!-- SISTEMA DE ALERTAS -->
        <?php if (isset($_GET['alerta'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                    $a = $_GET['alerta'];
                    if ($a === 'login_sucesso') echo "Log-in efetuado com sucesso!";
                    elseif ($a === 'sessao_terminada') echo "Sessão terminada com sucesso!";
                    elseif ($a === 'colaborador_adicionado') echo "Colaborador adicionado com sucesso!";
                    elseif ($a === 'colaborador_atualizado') echo "Colaborador atualizado com sucesso!";
                    elseif ($a === 'epi_adicionada') echo "EPI adicionada com sucesso!";
                    elseif ($a === 'epi_atualizado') echo "EPI atualizado com sucesso!";
                    elseif ($a === 'emprestimo_registrado') echo "Empréstimo registrado com sucesso!";
                    elseif ($a === 'perfil_atualizado') echo "Informações do utilizador atualizadas com sucesso!";
                    elseif ($a === 'excluido_com_sucesso') echo "Dado excluído com sucesso!";
                    else echo "Ação efetuada com sucesso!";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_GET['erro'] === 'nao_logado' ? "Função não disponível! Para liberar, é necessário realizar a entrada do utilizador no site." : "Ocorreu um erro ao processar a operação solicitada." ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- SOBRE A EMPRESA -->
        <section id="sobre" class="mb-5">
            <header class="text-center mb-4">
                <h1 class="fw-bold display-6 mb-3">Excelência e Inovação na Construção Civil</h1>
                <p class="lead text-muted mx-auto" style="max-width: 800px;">
                    A transformação de projetos complexos em estruturas sólidas é a base da nossa atuação. Execução de empreendimentos de médio e grande porte, com rigoroso compromisso com a excelência técnica e segurança do trabalho.
                </p>
            </header>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header card-header-primary">
                    <h4 class="mb-0 text-white fw-bold">Sistema de Gestão e Controle de Equipamentos de Proteção Individual (EPIs)</h4>
                </div>
                <div class="card-body p-4">
                    <p>O sistema visa automatizar e centralizar o controle de concessão, devolução e manutenção dos EPIs, proporcionando rastreabilidade, conformidade normativa e eficiência operacional.</p>
                </div>
            </div>
        </section>

        <!-- CARROSSEL -->
        <div class="mb-5">
            <div id="carouselExampleAutoplaying" class="carousel slide rounded-5 overflow-hidden shadow" data-bs-ride="carousel">
              <div class="carousel-inner">
                <div class="carousel-item active"><img src="fotos.epi/organizando projetos.jpg" class="d-block w-100" alt="Projeto" style="height: 350px; object-fit: cover;"></div>
                <div class="carousel-item"><img src="fotos.epi/dois constrtores.jpg" class="d-block w-100" alt="Obra" style="height: 350px; object-fit: cover;"></div>
                <div class="carousel-item"><img src="fotos.epi/trabalhadoresVerdes.jpg" class="d-block w-100" alt="Equipe" style="height: 350px; object-fit: cover;"></div>
              </div>
            </div>
        </div>
         <!-- CARD INSTITUCIONAL E FINALIDADE DO PORTAL -->
        <div class="card shadow-sm mb-5 border-0">
            <div class="card-header card-header-primary">
                <h4 class="mb-0 text-white fw-bold">🛡️ Sistema de Gestão e Controle de Equipamentos de Proteção Individual (EPIs)</h4>
            </div>
            <div class="card-body p-4">
                <p class="mb-3">
                    A conclusão de cada empreendimento reflete o nosso compromisso rigoroso com a <strong>excelência técnica</strong>, a <strong>pontualidade no cumprimento de cronogramas</strong>, a <strong>inovação em processos</strong> e a rigorosa observância às <strong>normas de segurança do trabalho</strong>. Acreditamos que a qualidade de uma edificação está diretamente associada ao planejamento estratégico e à valorização das equipes operacionais.
                </p>
                <p class="mb-4">
                    Com o objetivo de assegurar a integridade física dos colaboradores e otimizar os processos internos nos nossos canteiros de obras, esta plataforma destina-se à gestão integrada de Equipamentos de Proteção Individual (EPIs).
                </p>

                <h5 class="fw-bold mb-3 text-white border-bottom pb-2" style="border-color: var(--cor-borda) !important;">Finalidade do Portal</h5>
                <p class="mb-3">O sistema visa automatizar e centralizar o controle de concessão, devolução e manutenção dos EPIs, proporcionando:</p>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid var(--cor-borda);">
                            <h6 class="fw-bold text-white mb-2">📦 Rastreabilidade e Logística</h6>
                            <p class="small text-muted mb-0" style="color: var(--cor-texto-mutado) !important;">
                                Monitoramento do histórico de empréstimos, permitindo a identificação imediata dos responsáveis e a disponibilidade dos itens em estoque.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid var(--cor-borda);">
                            <h6 class="fw-bold text-white mb-2">📋 Conformidade Normativa</h6>
                            <p class="small text-muted mb-0" style="color: var(--cor-texto-mutado) !important;">
                                Garantia do cumprimento das exigências regulamentares relativas à segurança e saúde no trabalho.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid var(--cor-borda);">
                            <h6 class="fw-bold text-white mb-2">⚡ Eficiência Operacional</h6>
                            <p class="small text-muted mb-0" style="color: var(--cor-texto-mutado) !important;">
                                Mitigação de perdas, controle de desgaste dos materiais e agilidade no fornecimento dos equipamentos aos trabalhadores.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="fst-italic text-end mb-0 text-muted small" style="color: var(--cor-texto-mutado) !important;">
                    Esta iniciativa reforça a cultura de segurança, eficiência e responsabilidade corporativa da Empresa de construção civil.
                </p>
            </div>
        </div>

 <!-- REGISTRO DE EMPRÉSTIMOS -->
        <section id="emprestimos" class="mb-5">
            <div class="card shadow-sm border-0">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">📋 Registro de Empréstimos</h5>
                    <a href="#" onclick="verificarAcesso('form.php?tabela=emprestimos')" class="btn btn-sm btn-light fw-bold">+ Registrar Empréstimo</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr><th>#</th><th>Colaborador</th><th>EPI</th><th>Qtd</th><th>Data</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emprestimos as $emp): ?>
                                <tr>
                                    <th><?= $emp['id'] ?></th>
                                    <td><?= htmlspecialchars($emp['colaborador_nome']) ?></td>
                                    <td><?= htmlspecialchars($emp['epi_nome']) ?></td>
                                    <td><?= $emp['quantidade'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($emp['data_emprestimo'])) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= $emp['status'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- EQUIPAMENTOS (EPIS) -->
        <section id="epis" class="mb-5">
            <div class="card shadow-sm border-0">
                <div class="card-header card-header-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Equipamentos (EPIs)</h5>
                    <a href="form.php?tabela=epis" class="btn btn-sm btn-light fw-bold">+ Adicionar EPI</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>CA</th><th>Estoque</th><th class="text-end">Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($epis as $epi): ?>
                                <tr>
                                    <th><?= $epi['id'] ?></th>
                                    <td><?= htmlspecialchars($epi['nome']) ?></td>
                                    <td><?= htmlspecialchars($epi['CA']) ?></td>
                                    <td><strong><?= $epi['quantidade_estoque'] ?></strong> un</td>
                                    <td class="text-end">
                                        <a href="#" onclick="verificarAcesso('form.php?tabela=epis&id=<?= $epi['id'] ?>', event)" class="btn btn-sm btn-outline-warning">Editar</a>
                                        <a href="#" onclick="confirmarExclusao('acoes.php?acao=excluir&tabela=epis&id=<?= $epi['id'] ?>', event)" class="btn btn-sm btn-outline-danger">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- COLABORADORES -->
        <section id="colaboradores" class="mb-5">
            <div class="card shadow-sm border-0">
                <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Colaboradores</h5>
                    <a href="form.php?tabela=colaboradores" class="btn btn-sm btn-light fw-bold">+ Adicionar Colaborador</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>Cargo</th><th>Status</th><th class="text-end">Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colaboradores as $colab): ?>
                                <tr>
                                    <th><?= $colab['id'] ?></th>
                                    <td><?= htmlspecialchars($colab['nome']) ?></td>
                                    <td><?= htmlspecialchars($colab['cargo']) ?></td>
                                    <td><span class="<?= $colab['ativo'] ? 'text-success' : 'text-danger' ?>"><?= $colab['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                                    <td class="text-end">
                                        <a href="#" onclick="verificarAcesso('form.php?tabela=colaboradores&id=<?= $colab['id'] ?>', event)" class="btn btn-sm btn-outline-warning">Editar</a>
                                        <a href="#" onclick="confirmarExclusao('acoes.php?acao=excluir&tabela=colaboradores&id=<?= $colab['id'] ?>', event)" class="btn btn-sm btn-outline-danger">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>

    <script>
        const usuarioLogado = <?= $logado ? 'true' : 'false' ?>;

        function verificarAcesso(urlDestino, event) {
            if (event) event.preventDefault();
            if (!usuarioLogado) {
                alert("Função não disponível! Para liberar, é necessário realizar a entrada do utilizador no site.");
                window.location.href = "login.php";
            } else {
                window.location.href = urlDestino;
            }
        }

        function confirmarExclusao(urlExclusao, event) {
            if (event) event.preventDefault();
            if (!usuarioLogado) {
                alert("Função não disponível! Para liberar, é necessário realizar a entrada do utilizador no site.");
                window.location.href = "login.php";
            } else {
                if (confirm("Tem certeza de que deseja excluir este dado?")) {
                    window.location.href = urlExclusao;
                }
            }
        }

        document.getElementById('userArea').addEventListener('contextmenu', function(e) {
            e.preventDefault();
            if (!usuarioLogado) {
                alert("Para editar as informações, faça login primeiro.");
                window.location.href = "login.php";
            } else {
                window.location.href = "perfil.php";
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>