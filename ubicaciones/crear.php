<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/conexion.php';

$nombre = '';
$tipo = '';
$descripcion = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_ubicacion'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '' || $tipo === '') {
        $error = 'Nombre y tipo son obligatorios.';
    } elseif (!in_array($tipo, ['PC', 'OFICINA', 'BODEGA'], true)) {
        $error = 'Tipo de ubicacion no valido.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM ubicaciones WHERE LOWER(TRIM(nombre_ubicacion)) = LOWER(TRIM(?)) AND estado = "ACTIVO"');
            $stmt->execute([$nombre]);

            if ((int)$stmt->fetchColumn() > 0) {
                $error = 'Ya existe una ubicacion con ese nombre.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO ubicaciones (nombre_ubicacion, tipo, descripcion, estado) VALUES (?, ?, ?, "ACTIVO")');
                $stmt->execute([$nombre, $tipo, $descripcion]);
                header('Location: listar.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al guardar ubicacion.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Ubicacion | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Nueva Ubicacion</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="nombre_ubicacion" class="form-label">Nombre de ubicacion</label>
            <input type="text" class="form-control" id="nombre_ubicacion" name="nombre_ubicacion" required value="<?= htmlspecialchars($nombre) ?>">
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select" id="tipo" name="tipo" required>
                <option value="">Seleccione...</option>
                <option value="PC" <?= $tipo === 'PC' ? 'selected' : '' ?>>PC</option>
                <option value="OFICINA" <?= $tipo === 'OFICINA' ? 'selected' : '' ?>>OFICINA</option>
                <option value="BODEGA" <?= $tipo === 'BODEGA' ? 'selected' : '' ?>>BODEGA</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripcion</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($descripcion) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
