<?php
// gestion_unificada_editar.php
session_start();
if (!isset($_SESSION['id_admin'])) { http_response_code(403); exit('No autorizado'); }
require_once __DIR__ . '/config/conexion.php';

$id_detalle = $_POST['id_detalle'] ?? '';
$responsable = trim($_POST['responsable'] ?? '');
$id_ubicacion = $_POST['id_ubicacion'] ?? null;
$observacion = trim($_POST['observacion'] ?? '');

if ($id_detalle) {
    // Registrar un movimiento de edición para mantener historial
    $stmt = $pdo->prepare('SELECT id_producto FROM productos_detalle WHERE id_detalle = ?');
    $stmt->execute([$id_detalle]);
    $id_producto = $stmt->fetchColumn();
    if ($id_producto) {
        // Obtener el tipo_movimiento del último movimiento de este detalle
        $stmt = $pdo->prepare('SELECT m.tipo_movimiento FROM movimiento_detalle md JOIN movimientos m ON md.id_movimiento = m.id_movimiento WHERE md.id_detalle = ? ORDER BY m.fecha DESC LIMIT 1');
        $stmt->execute([$id_detalle]);
        $tipo_movimiento = $stmt->fetchColumn() ?: 'EDICION';
        $stmt = $pdo->prepare('INSERT INTO movimientos (id_producto, tipo_movimiento, cantidad, responsable, id_ubicacion, observacion) VALUES (?, ?, 1, ?, ?, ?)');
        $stmt->execute([$id_producto, $tipo_movimiento, $responsable, $id_ubicacion ?: null, $observacion]);
        $id_movimiento = $pdo->lastInsertId();
        $stmt = $pdo->prepare('INSERT INTO movimiento_detalle (id_movimiento, id_detalle) VALUES (?, ?)');
        $stmt->execute([$id_movimiento, $id_detalle]);
        echo 'OK';
        exit;
    }
}
echo 'Error';
