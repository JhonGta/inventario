<?php
// dashboard.php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/conexion.php';

// Totales
$total_productos = $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$total_categorias = $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
$total_ubicaciones = $pdo->query('SELECT COUNT(*) FROM ubicaciones')->fetchColumn();

// Últimos 10 movimientos
$stmt = $pdo->query('SELECT m.*, p.nombre AS producto, u.nombre_ubicacion FROM movimientos m
    JOIN productos p ON m.id_producto = p.id_producto
    LEFT JOIN ubicaciones u ON m.id_ubicacion = u.id_ubicacion
    ORDER BY m.fecha DESC LIMIT 10');
$ultimos_movimientos = $stmt->fetchAll();

// Productos con bajo stock (< 5)
$stmt = $pdo->query('SELECT * FROM productos WHERE stock_actual < 5');
$bajo_stock = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container mt-4">
    <h2>Dashboard</h2>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Productos</h5>
                    <p class="card-text fs-3"><?= $total_productos ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Categorías</h5>
                    <p class="card-text fs-3"><?= $total_categorias ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Ubicaciones</h5>
                    <p class="card-text fs-3"><?= $total_ubicaciones ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h4>Últimos 10 Movimientos</h4>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Responsable</th>
                        <th>Ubicación</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ultimos_movimientos as $mov): ?>
                    <tr>
                        <td><?= htmlspecialchars($mov['fecha']) ?></td>
                        <td><?= htmlspecialchars($mov['producto']) ?></td>
                        <td><?= htmlspecialchars($mov['tipo_movimiento']) ?></td>
                        <td><?= htmlspecialchars($mov['cantidad']) ?></td>
                        <td><?= htmlspecialchars($mov['responsable']) ?></td>
                        <td><?= htmlspecialchars($mov['nombre_ubicacion'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($mov['observacion']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h4>Productos con Bajo Stock</h4>
            <ul class="list-group">
                <?php foreach ($bajo_stock as $prod): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($prod['nombre']) ?>
                        <span class="badge bg-danger rounded-pill"><?= $prod['stock_actual'] ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($bajo_stock)): ?>
                    <li class="list-group-item">Sin productos con bajo stock</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
