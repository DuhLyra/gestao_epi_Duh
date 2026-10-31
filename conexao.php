<?php
// Lê as variáveis de ambiente (com valores predefinidos locais para testes)
$host     = getenv('DB_HOST') ?: 'localhost';
$port     = getenv('DB_PORT') ?: '3306';
$dbname   = getenv('DB_NAME') ?: 'gestao_epi';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$ca_cert  = getenv('DB_SSL_CA') ?: null;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Se for fornecido um certificado SSL (necessário no Aiven na nuvem)
if ($ca_cert && file_exists($ca_cert)) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ca_cert;
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password, $options);
} catch (PDOException $e) {
    die("Erro na conexão com a base de dados: " . $e->getMessage());
}
?>