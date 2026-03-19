<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$productos = $pdo->query('SELECT * FROM productos WHERE estado="ACTIVO" ORDER BY nombre')->fetchAll();
$ubicaciones = $pdo->query('SELECT * FROM ubicaciones WHERE estado = "ACTIVO" ORDER BY nombre_ubicacion')->fetchAll();
$error = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'] ?? '';
    $responsable = trim($_POST['responsable'] ?? '');
    $id_ubicacion = $_POST['id_ubicacion'] ?? '';
    $observacion = trim($_POST['observacion'] ?? '');
    $detalles = $_POST['id_detalle'] ?? [];
    // Si viene de acción directa (modal): solo una unidad, cantidad=1
    if ($id_producto && $responsable && is_array($detalles) && count($detalles) === 1) {
        $cantidad = 1;
        // Validar stock
        $stmt = $pdo->prepare('SELECT stock_actual FROM productos WHERE id_producto = ?');
        $stmt->execute([$id_producto]);
        $stock = $stmt->fetchColumn();
        if ($stock === false || $stock < 1) {
            $error = 'Stock insuficiente.';
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('UPDATE productos SET stock_actual = stock_actual - 1 WHERE id_producto = ?');
                $stmt->execute([$id_producto]);
                $stmt = $pdo->prepare('INSERT INTO movimientos (id_producto, tipo_movimiento, cantidad, responsable, id_ubicacion, observacion) VALUES (?, "SALIDA", 1, ?, ?, ?)');
                $stmt->execute([$id_producto, $responsable, $id_ubicacion ?: null, $observacion]);
                $id_movimiento = $pdo->lastInsertId();
                // Actualizar estado de la unidad y registrar en movimiento_detalle
                $stmt_det = $pdo->prepare('UPDATE productos_detalle SET estado = "ENTREGADO" WHERE id_detalle = ?');
                $stmt_mov_det = $pdo->prepare('INSERT INTO movimiento_detalle (id_movimiento, id_detalle) VALUES (?, ?)');
                $stmt_det->execute([$detalles[0]]);
                $stmt_mov_det->execute([$id_movimiento, $detalles[0]]);
                $pdo->commit();
                // Si es AJAX, terminar aquí
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { exit('OK'); }
                $msg = 'Salida registrada correctamente.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Error al registrar salida.';
            }
        }
    }
    // Modo tradicional (formulario completo)
    elseif ($id_producto && isset($_POST['cantidad']) && ($cantidad = (int)$_POST['cantidad']) > 0 && $responsable && count($detalles) == $cantidad) {
        // Validar stock
        $stmt = $pdo->prepare('SELECT stock_actual FROM productos WHERE id_producto = ?');
        $stmt->execute([$id_producto]);
        $stock = $stmt->fetchColumn();
        if ($stock === false || $stock < $cantidad) {
            $error = 'Stock insuficiente.';
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?');
                $stmt->execute([$cantidad, $id_producto]);
                $stmt = $pdo->prepare('INSERT INTO movimientos (id_producto, tipo_movimiento, cantidad, responsable, id_ubicacion, observacion) VALUES (?, "SALIDA", ?, ?, ?, ?)');
                $stmt->execute([$id_producto, $cantidad, $responsable, $id_ubicacion ?: null, $observacion]);
                $id_movimiento = $pdo->lastInsertId();
                // Actualizar estado de los detalles seleccionados y registrar en movimiento_detalle
                $stmt_det = $pdo->prepare('UPDATE productos_detalle SET estado = "ENTREGADO" WHERE id_detalle = ?');
                $stmt_mov_det = $pdo->prepare('INSERT INTO movimiento_detalle (id_movimiento, id_detalle) VALUES (?, ?)');
                foreach ($detalles as $id_det) {
                    $stmt_det->execute([$id_det]);
                    $stmt_mov_det->execute([$id_movimiento, $id_det]);
                }
                $pdo->commit();
                $msg = 'Salida registrada correctamente.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Error al registrar salida.';
            }
        }
    } else {
        $error = 'Todos los campos son obligatorios, cantidad > 0 y debes seleccionar las unidades.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salida de Inventario | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <h3>Registrar Salida</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post" id="formSalida">
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
            <label class="form-label">Selecciona las unidades a entregar</label>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Serie</th>
                    </tr>
                </thead>
                <tbody id="detallesTableBody"></tbody>
            </table>
            <div class="form-text">Marca las unidades a entregar. La cantidad se ajusta automáticamente.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Cantidad</label>
            <input type="number" name="cantidad" class="form-control" min="1" required id="cantidadInput" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Responsable</label>
            <input type="text" name="responsable" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Ubicación (opcional)</label>
            <div class="input-group">
                <select name="id_ubicacion" id="id_ubicacion" class="form-select">
                    <option value="">Sin ubicación</option>
                    <?php foreach ($ubicaciones as $ubi): ?>
                        <option value="<?= $ubi['id_ubicacion'] ?>"><?= htmlspecialchars($ubi['nombre_ubicacion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-text">Si es instalación en PC/oficina, seleccione ubicación. Si es entrega a persona, deje vacío.</div>
            <button type="button" class="btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalUbicacion">+ Agregar Ubicación</button>
        </div>
        <div class="mb-3">
            <label class="form-label">Observación</label>
            <textarea name="observacion" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-danger" id="btnRegistrarSalida">Registrar Salida</button>
            <a href="../dashboard.php" class="btn btn-secondary">Volver</a>
        </div>
    </form>
    <!-- Modal para agregar ubicación -->
    <div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUbicacionLabel">Agregar Nueva Ubicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formUbicacionModal" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre_ubicacion" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo_ubicacion" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="PC">PC</option>
                                <option value="OFICINA">OFICINA</option>
                                <option value="BODEGA">BODEGA</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control"></textarea>
                        </div>
                        <div id="msgUbicacion" class="text-danger small mb-2"></div>
                        <button type="button" class="btn btn-primary" id="btnGuardarUbicacionModal">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Validación nativa HTML5, sin JS personalizado -->
<script>
// Detalles dinámicos por producto
var detallesPorProducto = {};
<?php
foreach ($productos as $prod) {
    $stmt_det = $pdo->prepare("SELECT * FROM productos_detalle WHERE id_producto = ? AND (estado = 'ACTIVO' OR estado = 'DEVUELTO')");
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
    var cantidadInput = document.getElementById('cantidadInput');
    detallesTableBody.innerHTML = '';
    cantidadInput.value = '';
    if (detalles.length > 0) {
        detallesContainer.style.display = '';
        detalles.forEach(function(det, i) {
            var row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox" name="id_detalle[]" value="${det.id_detalle}" onchange="actualizarCantidad()"></td>
                <td>${det.nombre ? det.nombre : ''}</td>
                <td>${det.marca ? det.marca : ''}</td>
                <td>${det.serie ? det.serie : ''}</td>
            `;
            detallesTableBody.appendChild(row);
        });
    } else {
        detallesContainer.style.display = 'none';
    }
});

window.actualizarCantidad = function() {
    var checked = document.querySelectorAll('input[name="id_detalle[]"]:checked');
    document.getElementById('cantidadInput').value = checked.length;
};

document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'btnGuardarUbicacionModal') {
        var formUbicacion = e.target.closest('form');
        if (!formUbicacion) return;
        var datos = new FormData(formUbicacion);
        // Validación manual
        var nombreNuevo = datos.get('nombre_ubicacion').trim().toLowerCase();
        var select = document.getElementById('id_ubicacion');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].textContent.trim().toLowerCase() === nombreNuevo) {
                document.getElementById('msgUbicacion').textContent = 'Ya existe una ubicación con ese nombre.';
                return;
            }
        }
        if (!nombreNuevo || !datos.get('tipo_ubicacion')) {
            document.getElementById('msgUbicacion').textContent = 'Nombre y tipo son obligatorios.';
            return;
        }
        // Renombrar el campo para el backend
        if (datos.has('tipo_ubicacion')) {
            datos.append('tipo', datos.get('tipo_ubicacion'));
        }
        fetch('../ubicaciones/ajax_agregar.php', {
            method: 'POST',
            body: datos
        })
        .then(function(r) {
            if (!r.ok) throw new Error('No se pudo enviar la petición');
            return r.json();
        })
        .then(function(res) {
            if (res.success) {
                // Agregar la nueva ubicación al select
                var select = document.getElementById('id_ubicacion');
                var opt = document.createElement('option');
                opt.value = res.id;
                opt.textContent = res.nombre;
                // Eliminar duplicados de la opción recién agregada
                for (var i = 0; i < select.options.length - 1; i++) {
                    if (select.options[i].value === res.id) {
                        select.remove(i);
                        break;
                    }
                }
                select.appendChild(opt);
                var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacion'));
                modal.hide();
                var backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(b) { b.remove(); });
                // Seleccionar la nueva ubicación después de cerrar el modal
                setTimeout(function() {
                    select.value = res.id;
                    select.dispatchEvent(new Event('change'));
                }, 100);
                formUbicacion.reset();
                document.getElementById('msgUbicacion').textContent = '';
            } else {
                document.getElementById('msgUbicacion').textContent = res.msg || 'Error.';
            }
        })
        .catch(function(e) {
            document.getElementById('msgUbicacion').textContent = 'Error de red o petición: ' + e.message;
        });
    }
});
</script>
<!-- Fin del archivo -->
