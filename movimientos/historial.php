<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';

$stmt = $pdo->query('SELECT m.*, p.nombre AS producto, u.nombre_ubicacion FROM movimientos m
    JOIN productos p ON m.id_producto = p.id_producto
    LEFT JOIN ubicaciones u ON m.id_ubicacion = u.id_ubicacion
    ORDER BY m.fecha DESC');
$movimientos = $stmt->fetchAll();

// Obtener detalles por movimiento
$detallesPorMovimiento = [];
foreach ($movimientos as $mov) {
    $stmt_det = $pdo->prepare('SELECT pd.nombre, pd.marca, pd.serie FROM movimiento_detalle md JOIN productos_detalle pd ON md.id_detalle = pd.id_detalle WHERE md.id_movimiento = ?');
    $stmt_det->execute([$mov['id_movimiento']]);
    $detallesPorMovimiento[$mov['id_movimiento']] = $stmt_det->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Movimientos | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Historial de Movimientos</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPDF">Descargar Reporte PDF</button>
    </div>
    <!-- Modal para visualizar PDF -->
    <div class="modal fade" id="modalPDF" tabindex="-1" aria-labelledby="modalPDFLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalPDFLabel">Reporte de Inventario PDF</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" style="height:80vh;">
            <iframe src="reporte_pdf.php" width="100%" height="100%" style="border:none;"></iframe>
          </div>
        </div>
      </div>
    </div>
    <form method="get" class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Fecha desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo de movimiento</label>
            <select name="tipo_movimiento" class="form-select">
                <option value="">Todos</option>
                <option value="ENTRADA" <?= (($_GET['tipo_movimiento'] ?? '') === 'ENTRADA') ? 'selected' : '' ?>>ENTRADA</option>
                <option value="SALIDA" <?= (($_GET['tipo_movimiento'] ?? '') === 'SALIDA') ? 'selected' : '' ?>>SALIDA</option>
                <option value="DEVOLUCION" <?= (($_GET['tipo_movimiento'] ?? '') === 'DEVOLUCION') ? 'selected' : '' ?>>DEVOLUCIÓN</option>
                <option value="BAJA" <?= (($_GET['tipo_movimiento'] ?? '') === 'BAJA') ? 'selected' : '' ?>>BAJA</option>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="historial.php" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>

    <table class="table table-bordered table-hover table-sm">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Marca</th>
                <th>Serie</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Responsable</th>
                <th>Ubicación</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Filtrar movimientos
        $fecha_desde = $_GET['fecha_desde'] ?? '';
        $fecha_hasta = $_GET['fecha_hasta'] ?? '';
        $tipo_movimiento = $_GET['tipo_movimiento'] ?? '';
        foreach ($movimientos as $mov):
            $mostrar = true;
            if ($fecha_desde && $mov['fecha'] < $fecha_desde.' 00:00:00') $mostrar = false;
            if ($fecha_hasta && $mov['fecha'] > $fecha_hasta.' 23:59:59') $mostrar = false;
            if ($tipo_movimiento && $mov['tipo_movimiento'] !== $tipo_movimiento) $mostrar = false;
            if (!$mostrar) continue;
        ?>
            <tr>
                <td><?= htmlspecialchars($mov['fecha']) ?></td>
                <td><?= htmlspecialchars($mov['producto']) ?></td>
                <td>
                    <?php
                    $detalles = $detallesPorMovimiento[$mov['id_movimiento']] ?? [];
                    $marcas = array_map(function($d) { return $d['marca']; }, $detalles);
                    echo $marcas ? htmlspecialchars(implode(', ', array_unique($marcas))) : '-';
                    ?>
                </td>
                <td>
                    <?php
                    $detalles = $detallesPorMovimiento[$mov['id_movimiento']] ?? [];
                    $series = array_map(function($d) { return $d['serie']; }, $detalles);
                    echo $series ? htmlspecialchars(implode(', ', array_unique($series))) : '-';
                    ?>
                </td>
                <td>
                    <?php
                        if ($mov['tipo_movimiento'] === 'ENTRADA') {
                            echo '<span class="badge bg-success">ENTRADA</span>';
                        } elseif ($mov['tipo_movimiento'] === 'SALIDA') {
                            echo '<span class="badge bg-danger">SALIDA</span>';
                        } elseif ($mov['tipo_movimiento'] === 'DEVOLUCION') {
                            echo '<span class="badge bg-info text-dark">DEVOLUCIÓN</span>';
                        } elseif ($mov['tipo_movimiento'] === 'BAJA') {
                            echo '<span class="badge bg-secondary">BAJA</span>';
                        } else {
                            echo htmlspecialchars($mov['tipo_movimiento']);
                        }
                    ?>
                </td>
                <td><?= htmlspecialchars($mov['cantidad']) ?></td>
                <td><?= htmlspecialchars($mov['responsable']) ?></td>
                <td><?= htmlspecialchars($mov['nombre_ubicacion'] ?? '-') ?></td>
                <td><?= htmlspecialchars($mov['observacion']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
