<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói o cookie de sessão do navegador, se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Cabeçalhos HTTP para forçar o navegador a limpar o cache da página restrita
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redireciona para a página inicial informando o término
header("Location: index.php?alerta=sessao_terminada");
exit;
?>