<?php
$candidatos = [
    [
        'fuente' => 'vars_internas',
        'host' => getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306,
        'dbname' => getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'inventario',
        'user' => getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root',
        'pass' => getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '',
        'ssl' => false,
    ],
];

foreach (['MYSQL_URL', 'DATABASE_URL', 'MYSQL_PUBLIC_URL'] as $nombreVariable) {
    $valor = getenv($nombreVariable);
    if ($valor) {
        $parts = parse_url($valor);
        if ($parts !== false) {
            $candidatos[] = [
                'fuente' => $nombreVariable,
                'host' => $parts['host'] ?? 'localhost',
                'port' => $parts['port'] ?? 3306,
                'dbname' => isset($parts['path']) ? ltrim($parts['path'], '/') : 'inventario',
                'user' => $parts['user'] ?? 'root',
                'pass' => $parts['pass'] ?? '',
                'ssl' => $nombreVariable === 'MYSQL_PUBLIC_URL',
            ];
        }
    }
}

$pdo = null;
$ultimoError = null;

foreach ($candidatos as $candidato) {
    $dsn = "mysql:host={$candidato['host']};port={$candidato['port']};dbname={$candidato['dbname']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];

    if ($candidato['ssl'] && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    try {
        $pdo = new PDO($dsn, $candidato['user'], $candidato['pass'], $options);
        break;
    } catch (PDOException $e) {
        $ultimoError = $e;
    }
}

if (!$pdo) {
    die('Error de conexion: ' . ($ultimoError ? $ultimoError->getMessage() : 'No fue posible conectar a la base de datos.'));
}
