<?php
// config/conexion.php
// Conexión PDO preparada para XAMPP y Railway

$DB_HOST = getenv('MYSQLHOST') ?: 'localhost';
$DB_NAME = getenv('MYSQLDATABASE') ?: 'inventario';
$DB_USER = getenv('MYSQLUSER') ?: 'root';
$DB_PASS = getenv('MYSQLPASSWORD') ?: '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // No mostrar detalles sensibles en producción
    exit('Error de conexión a la base de datos.');
}
