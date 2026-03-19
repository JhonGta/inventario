<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM productos WHERE id_producto = ?');
$stmt->execute([$id]);
$prod = $stmt->fetch();
if (!$prod) { header('Location: listar.php'); exit; }
$cats = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
$nombre = $prod['nombre'];
$descripcion = $prod['descripcion'];
$id_categoria = $prod['id_categoria'];
$estado = $prod['estado'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_categoria = $_POST['id_categoria'] ?? '';
    $estado = $_POST['estado'] ?? 'ACTIVO';
    if ($nombre && $id_categoria) {
        try {
            $stmt = $pdo->prepare('UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, estado=? WHERE id_producto=?');
            $stmt->execute([$nombre, $descripcion, $id_categoria, $estado, $id]);
            header('Location: listar.php'); exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar.';
        }
    } else {
        $error = 'Nombre y categoría son obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Editar Producto</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($nombre) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control"><?= htmlspecialchars($descripcion) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="id_categoria" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat['id_categoria'] ?>" <?= $id_categoria == $cat['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="ACTIVO" <?= $estado == 'ACTIVO' ? 'selected' : '' ?>>ACTIVO</option>
                <option value="INACTIVO" <?= $estado == 'INACTIVO' ? 'selected' : '' ?>>INACTIVO</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="listar.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
