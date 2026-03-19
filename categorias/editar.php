<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: listar.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM categorias WHERE id_categoria = ?');
$stmt->execute([$id]);
$categoria = $stmt->fetch();
if (!$categoria) { header('Location: listar.php'); exit; }
$nombre = $categoria['nombre'];
$descripcion = $categoria['descripcion'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if ($nombre) {
        try {
            $stmt = $pdo->prepare('UPDATE categorias SET nombre=?, descripcion=? WHERE id_categoria=?');
            $stmt->execute([$nombre, $descripcion, $id]);
            header('Location: listar.php'); exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar.';
        }
    } else {
        $error = 'El nombre es obligatorio.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Categoría | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Editar Categoría</h3>
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
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="listar.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
