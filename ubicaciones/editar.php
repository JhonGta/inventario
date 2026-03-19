<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM ubicaciones WHERE id_ubicacion = ?');
$stmt->execute([$id]);
$ubi = $stmt->fetch();
if (!$ubi) { header('Location: listar.php'); exit; }
$nombre = $ubi['nombre_ubicacion'];
$tipo = $ubi['tipo'];
$descripcion = $ubi['descripcion'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_ubicacion'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? '');
    if ($nombre && $tipo) {
        try {
            $stmt = $pdo->prepare('UPDATE ubicaciones SET nombre_ubicacion=?, tipo=?, descripcion=? WHERE id_ubicacion=?');
            $stmt->execute([$nombre, $tipo, $descripcion, $id]);
            header('Location: listar.php'); exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar.';
        }
    } else {
        $error = 'Nombre y tipo son obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Ubicación | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Editar Ubicación</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre_ubicacion" class="form-control" required value="<?= htmlspecialchars($nombre) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="PC" <?= $tipo == 'PC' ? 'selected' : '' ?>>PC</option>
                <option value="OFICINA" <?= $tipo == 'OFICINA' ? 'selected' : '' ?>>OFICINA</option>
                <option value="BODEGA" <?= $tipo == 'BODEGA' ? 'selected' : '' ?>>BODEGA</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control"><?= htmlspecialchars($descripcion) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="listar.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
