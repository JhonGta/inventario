<?php
echo "PHP funciona correctamente<br><br>";

// Prueba conexión a BD
try {
    $DB_HOST = getenv('MYSQLHOST') ?: 'localhost';
    $DB_NAME = getenv('MYSQLDATABASE') ?: 'inventario';
    $DB_USER = getenv('MYSQLUSER') ?: 'root';
    $DB_PASS = getenv('MYSQLPASSWORD') ?: '';
    $DB_PORT = getenv('MYSQLPORT') ?: 3306;
    
    echo "Variables de entorno:<br>";
    echo "HOST: " . $DB_HOST . "<br>";
    echo "USER: " . $DB_USER . "<br>";
    echo "DB: " . $DB_NAME . "<br>";
    echo "PORT: " . $DB_PORT . "<br><br>";
    
    $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);
    echo "Conexion a BD exitosa!";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
