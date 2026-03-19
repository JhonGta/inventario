<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
// Obtener solo categorías activas
$cats = $pdo->query("SELECT * FROM categorias WHERE estado = 'ACTIVO' ORDER BY nombre")->fetchAll();
$nombre = $descripcion = $id_categoria = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $id_categoria = $_POST['id_categoria'] ?? '';
    if ($nombre && $id_categoria) {
        try {
            $stmt = $pdo->prepare('INSERT INTO productos (nombre, descripcion, id_categoria, stock_actual, estado) VALUES (?, ?, ?, 0, "ACTIVO")');
            $stmt->execute([$nombre, $descripcion, $id_categoria]);
            header('Location: listar.php'); exit;
        } catch (PDOException $e) {
            $error = 'Error al crear producto.';
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
    <title>Nuevo Producto | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Nuevo Producto</h3>
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
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="listar.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
