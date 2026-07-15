<?php
echo "PHP funciona correctamente<br><br>";

// Prueba conexión a BD
try {
    $fuentes = [
        'vars_internas' => [
            'host' => getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost',
            'port' => getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306,
            'dbname' => getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'inventario',
            'user' => getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root',
            'pass' => getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '',
        ],
        'mysql_url' => getenv('MYSQL_URL') ? parse_url(getenv('MYSQL_URL')) : false,
        'database_url' => getenv('DATABASE_URL') ? parse_url(getenv('DATABASE_URL')) : false,
        'mysql_public_url' => getenv('MYSQL_PUBLIC_URL') ? parse_url(getenv('MYSQL_PUBLIC_URL')) : false,
    ];

    $intentos = [];

    $candidatos = [];

    $candidatos[] = [
        'fuente' => 'vars_internas',
        'host' => $fuentes['vars_internas']['host'],
        'port' => $fuentes['vars_internas']['port'],
        'dbname' => $fuentes['vars_internas']['dbname'],
        'user' => $fuentes['vars_internas']['user'],
        'pass' => $fuentes['vars_internas']['pass'],
        'ssl' => false,
    ];

    foreach (['mysql_url', 'database_url', 'mysql_public_url'] as $fuente) {
        if ($fuentes[$fuente] !== false) {
            $parts = $fuentes[$fuente];
            $candidatos[] = [
                'fuente' => $fuente,
                'host' => $parts['host'] ?? 'localhost',
                'port' => $parts['port'] ?? 3306,
                'dbname' => isset($parts['path']) ? ltrim($parts['path'], '/') : 'inventario',
                'user' => $parts['user'] ?? 'root',
                'pass' => $parts['pass'] ?? '',
                'ssl' => $fuente === 'mysql_public_url',
            ];
        }
    }

    $pdo = null;
    $ultimoError = null;
    $fuenteUsada = null;

    foreach ($candidatos as $candidato) {
        $dsn = "mysql:host={$candidato['host']};port={$candidato['port']};dbname={$candidato['dbname']};charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ];

        if ($candidato['ssl'] && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        try {
            $pdo = new PDO($dsn, $candidato['user'], $candidato['pass'], $opciones);
            $pdo->query('SELECT 1');
            $fuenteUsada = $candidato;
            break;
        } catch (PDOException $e) {
            $ultimoError = $e;
            $intentos[] = $candidato['fuente'] . ': ' . $e->getMessage();
        }
    }

    echo "Variables de entorno:<br>";
    echo "HOST: " . ($fuenteUsada['host'] ?? $candidatos[0]['host']) . "<br>";
    echo "USER: " . ($fuenteUsada['user'] ?? $candidatos[0]['user']) . "<br>";
    echo "DB: " . ($fuenteUsada['dbname'] ?? $candidatos[0]['dbname']) . "<br>";
    echo "PORT: " . ($fuenteUsada['port'] ?? $candidatos[0]['port']) . "<br>";
    echo "FUENTE: " . ($fuenteUsada['fuente'] ?? 'ninguna') . "<br><br>";

    if (!$pdo) {
        throw $ultimoError ?? new PDOException('No fue posible conectar a la base de datos.');
    }

    echo "Conexion a BD exitosa!";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    if (!empty($intentos)) {
        echo "<br><br>Intentos:<br>" . implode('<br>', $intentos);
    }
}
?>
