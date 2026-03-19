<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$productos = $pdo->query('SELECT * FROM productos WHERE estado="ACTIVO" ORDER BY nombre')->fetchAll();
$error = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'] ?? '';
    $detalles = $_POST['id_detalle'] ?? [];
    $observacion = $_POST['observacion'] ?? '';
    if ($id_producto && count($detalles) > 0) {
        try {
            $pdo->beginTransaction();
            // Actualizar estado de los detalles seleccionados
            $stmt_det = $pdo->prepare('UPDATE productos_detalle SET estado = "BAJA" WHERE id_detalle = ?');
            foreach ($detalles as $id_det) {
                $stmt_det->execute([$id_det]);
            }
            // Restar stock
            $stmt = $pdo->prepare('UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?');
            $stmt->execute([count($detalles), $id_producto]);
            // Registrar movimiento de baja
            $stmt_mov = $pdo->prepare('INSERT INTO movimientos (id_producto, tipo_movimiento, cantidad, responsable, id_ubicacion, observacion) VALUES (?, "BAJA", ?, ?, NULL, ?)');
            $stmt_mov->execute([$id_producto, count($detalles), $_SESSION['nombre'], $observacion]);
            $id_movimiento = $pdo->lastInsertId();
            // Registrar cada unidad en movimiento_detalle
            $stmt_mov_det = $pdo->prepare('INSERT INTO movimiento_detalle (id_movimiento, id_detalle) VALUES (?, ?)');
            foreach ($detalles as $id_det) {
                $stmt_mov_det->execute([$id_movimiento, $id_det]);
            }
            $pdo->commit();
            $msg = 'Baja registrada correctamente.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al registrar baja.';
        }
    } else {
        $error = 'Debes seleccionar el producto y las unidades a dar de baja.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Baja de Producto | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Registrar Baja de Producto</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post" id="formBaja">
        <div class="mb-3">
            <label class="form-label">Producto</label>
            <select name="id_producto" class="form-select" required id="productoSelect">
                <option value="">Seleccione...</option>
                <?php foreach ($productos as $prod): ?>
                    <option value="<?= $prod['id_producto'] ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3" id="detallesContainer" style="display:none">
            <label class="form-label">Selecciona las unidades a dar de baja</label>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Serie</th>
                        <th>Estado actual</th>
                    </tr>
                </thead>
                <tbody id="detallesTableBody"></tbody>
            </table>
            <div class="form-text">Marca las unidades a dar de baja.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Observación</label>
            <textarea name="observacion" class="form-control" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-danger">Registrar Baja</button>
            <a href="../dashboard.php" class="btn btn-secondary">Volver</a>
        </div>
    </form>
</div>
<script>
var detallesPorProducto = {};
<?php
foreach ($productos as $prod) {
    $stmt_det = $pdo->prepare("SELECT * FROM productos_detalle WHERE id_producto = ? AND estado = 'ACTIVO'");
    $stmt_det->execute([$prod['id_producto']]);
    $detalles = $stmt_det->fetchAll();
    echo "detallesPorProducto['{$prod['id_producto']}'] = ".json_encode($detalles).";\n";
}
?>
document.getElementById('productoSelect').addEventListener('change', function() {
    var id = this.value;
    var detalles = detallesPorProducto[id] || [];
    var detallesContainer = document.getElementById('detallesContainer');
    var detallesTableBody = document.getElementById('detallesTableBody');
    detallesTableBody.innerHTML = '';
    if (detalles.length > 0) {
        detallesContainer.style.display = '';
        detalles.forEach(function(det, i) {
            var row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox" name="id_detalle[]" value="${det.id_detalle}"></td>
                <td>${det.nombre ? det.nombre : ''}</td>
                <td>${det.marca ? det.marca : ''}</td>
                <td>${det.serie ? det.serie : ''}</td>
                <td>${det.estado}</td>
            `;
            detallesTableBody.appendChild(row);
        });
    } else {
        detallesContainer.style.display = 'none';
    }
});
</script>
</body>
</html>
