
<?php
// Configuración flexible para Railway y local
$DB_HOST = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'inventario';
$DB_USER = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$DB_PORT = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die('Error de conexion: ' . $e->getMessage());
}
