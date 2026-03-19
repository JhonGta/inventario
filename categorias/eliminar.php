<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar.php'); exit; }
// No permitir eliminar si hay productos asociados
$stmt = $pdo->prepare('SELECT COUNT(*) FROM productos WHERE id_categoria = ?');
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    header('Location: listar.php?error=asociada'); exit;
}
$stmt = $pdo->prepare("UPDATE categorias SET estado = 'INACTIVO' WHERE id_categoria = ?");
$stmt->execute([$id]);
header('Location: listar.php');
exit;
