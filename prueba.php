<?php
echo "PHP funciona correctamente<br><br>";

// Prueba conexión a BD
try {
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
        $DB_HOST = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
        $DB_NAME = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'inventario';
        $DB_USER = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
        $DB_PASS = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
        $DB_PORT = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;
    }
    
    echo "Variables de entorno:<br>";
    echo "HOST: " . $DB_HOST . "<br>";
    echo "USER: " . $DB_USER . "<br>";
    echo "DB: " . $DB_NAME . "<br>";
    echo "PORT: " . $DB_PORT . "<br><br>";
    
    $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $pdo->query('SELECT 1');
    echo "Conexion a BD exitosa!";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
