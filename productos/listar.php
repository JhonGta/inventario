<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$stmt = $pdo->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.estado = 'ACTIVO' ORDER BY p.nombre");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Productos</h3>
        <a href="crear.php" class="btn btn-primary">Nuevo Producto</a>
    </div>
    <table class="table table-bordered table-hover">
        <thead>
            <tr><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $prod): ?>
            <tr>
                <td><?= htmlspecialchars($prod['nombre']) ?></td>
                <td><?= htmlspecialchars($prod['categoria']) ?></td>
                <td><?= $prod['stock_actual'] ?></td>
                <td><?= $prod['estado'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $prod['id_producto'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="eliminar.php?id=<?= $prod['id_producto'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                    <button class="btn btn-sm btn-info ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#detalles<?= $prod['id_producto'] ?>" aria-expanded="false" aria-controls="detalles<?= $prod['id_producto'] ?>">Ver detalles</button>
                </td>
            </tr>
            <?php
            $stmt_det = $pdo->prepare("SELECT * FROM productos_detalle WHERE id_producto = ? AND (estado = 'ACTIVO' OR estado = 'DEVUELTO')");
            $stmt_det->execute([$prod['id_producto']]);
            $detalles = $stmt_det->fetchAll();
            ?>
            <tr class="collapse" id="detalles<?= $prod['id_producto'] ?>">
                <td colspan="5">
                    <div class="ms-4">
                        <b>Unidades en stock:</b>
                        <table class="table table-sm table-bordered mb-0 mt-2">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Marca</th>
                                    <th>Serie</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $i => $det): ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($det['nombre']) ?></td>
                                    <td><?= htmlspecialchars($det['marca']) ?></td>
                                    <td><?= htmlspecialchars($det['serie']) ?></td>
                                    <td><?= htmlspecialchars($det['estado']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
