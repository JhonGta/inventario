<?php
$databaseUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('MYSQL_PUBLIC_URL');

if ($databaseUrl) {
    $parts = parse_url($databaseUrl);

    if ($parts !== false) {
        $DB_HOST = $parts['host'] ?? 'localhost';
        $DB_PORT = $parts['port'] ?? 3306;
        $DB_NAME = isset($parts['path']) ? ltrim($parts['path'], '/') : 'inventario';
        $DB_USER = $parts['user'] ?? 'root';
        $DB_PASS = $parts['pass'] ?? '';
    }
}

if (!isset($DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS)) {
    $DB_HOST = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost';
    $DB_NAME = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'inventario';
    $DB_USER = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
    $DB_PASS = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '';
    $DB_PORT = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306;
}

$DB_CHARSET = 'utf8mb4';
$dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_TIMEOUT => 5,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die('Error de conexion: ' . $e->getMessage());
}
