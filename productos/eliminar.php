<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar.php'); exit; }
// No permitir eliminar si hay movimientos asociados
$stmt = $pdo->prepare('SELECT COUNT(*) FROM movimientos WHERE id_producto = ?');
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    header('Location: listar.php?error=asociado'); exit;
}
$stmt = $pdo->prepare("UPDATE productos SET estado = 'INACTIVO' WHERE id_producto = ?");
$stmt->execute([$id]);
header('Location: listar.php');
exit;
