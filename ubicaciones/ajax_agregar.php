<?php
// ubicaciones/ajax_agregar.php
session_start();
if (!isset($_SESSION['id_admin'])) { http_response_code(403); exit; }
require_once '../config/conexion.php';
header('Content-Type: application/json');
$nombre = trim($_POST['nombre_ubicacion'] ?? '');
$tipo = $_POST['tipo'] ?? '';
$descripcion = trim($_POST['descripcion'] ?? '');
if ($nombre && $tipo) {
    // Validar nombre único
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ubicaciones WHERE LOWER(TRIM(nombre_ubicacion)) = LOWER(TRIM(?)) AND estado = "ACTIVO"');
    $stmt->execute([$nombre]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'msg' => 'Ya existe una ubicación con ese nombre.']);
        exit;
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO ubicaciones (nombre_ubicacion, tipo, descripcion, estado) VALUES (?, ?, ?, "ACTIVO")');
        $stmt->execute([$nombre, $tipo, $descripcion]);
        $id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id, 'nombre' => $nombre]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'msg' => 'Error al guardar ubicación: ' . $e->getMessage()]);
    }
} else {
    echo json_encode([
        'success' => false,
        'msg' => 'Nombre y tipo son obligatorios.',
        'debug' => [
            'POST' => $_POST,
            'nombre_ubicacion' => $nombre,
            'tipo' => $tipo,
            'descripcion' => $descripcion
        ]
    ]);
}
