<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/config/conexion.php';

// Filtros
$estadoFiltro = $_GET['estado'] ?? 'STOCK';
$estados = [
    'STOCK' => 'En Stock',
    'ENTREGADO' => 'Entregados',
    'BAJA' => 'Dados de Baja',
    'DEVUELTO' => 'Devueltos',
    'TODOS' => 'Todos'
];
$where = '';
switch ($estadoFiltro) {
    case 'STOCK':
        $where = "(pd.estado = 'ACTIVO' OR pd.estado = 'DEVUELTO')";
        break;
    case 'ENTREGADO':
        $where = "pd.estado = 'ENTREGADO'";
        break;
    case 'BAJA':
        $where = "pd.estado = 'BAJA'";
        break;
    case 'DEVUELTO':
        $where = "pd.estado = 'DEVUELTO'";
        break;
    case 'TODOS':
    default:
        $where = '1=1';
}

$sql = "SELECT pd.*, p.nombre AS producto, c.nombre AS categoria
        FROM productos_detalle pd
        JOIN productos p ON pd.id_producto = p.id_producto
        JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE $where
        ORDER BY p.nombre, pd.id_detalle DESC";
$detalles = $pdo->query($sql)->fetchAll();

// Obtener último movimiento de cada detalle
$movimientos = [];
foreach ($detalles as $d) {
    $stmt = $pdo->prepare('SELECT m.*, u.nombre_ubicacion FROM movimiento_detalle md JOIN movimientos m ON md.id_movimiento = m.id_movimiento LEFT JOIN ubicaciones u ON m.id_ubicacion = u.id_ubicacion WHERE md.id_detalle = ? ORDER BY m.fecha DESC LIMIT 1');
    $stmt->execute([$d['id_detalle']]);
    $movimientos[$d['id_detalle']] = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Unificada | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Gestión Unificada de Inventario</h3>
        <form method="get" class="d-flex align-items-center gap-2">
            <label for="estado" class="form-label mb-0">Filtrar por estado:</label>
            <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                <?php foreach ($estados as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $estadoFiltro == $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Serie</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Ubicación</th>
                <th>Observación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalles as $d): 
            $mov = $movimientos[$d['id_detalle']] ?? null;
            $responsable = $mov['responsable'] ?? '-';
            $ubicacion = $mov['nombre_ubicacion'] ?? '-';
            $observacion = $mov['observacion'] ?? '-';
        ?>
            <tr>
                <td><?= htmlspecialchars($d['producto']) ?></td>
                <td><?= htmlspecialchars($d['categoria']) ?></td>
                <td><?= htmlspecialchars($d['marca']) ?></td>
                <td><?= htmlspecialchars($d['serie']) ?></td>
                <td><?= htmlspecialchars($d['estado']) ?></td>
                <td><?= htmlspecialchars($responsable) ?></td>
                <td><?= htmlspecialchars($ubicacion) ?></td>
                <td><?= htmlspecialchars($observacion) ?></td>
                <td>
                    <button class="btn btn-sm btn-primary mb-1 btn-editar" data-id="<?= $d['id_detalle'] ?>">Editar</button>
                    <?php if ($d['estado'] === 'ACTIVO' || $d['estado'] === 'DEVUELTO'): ?>
                        <button class="btn btn-sm btn-danger mb-1 btn-salida" data-id="<?= $d['id_detalle'] ?>">Salida</button>
                        <button class="btn btn-sm btn-secondary mb-1 btn-baja" data-id="<?= $d['id_detalle'] ?>">Baja</button>
                    <?php elseif ($d['estado'] === 'ENTREGADO'): ?>
                        <button class="btn btn-sm btn-success mb-1 btn-devolucion" data-id="<?= $d['id_detalle'] ?>">Devolución</button>
                    <?php else: ?>
                        <span class="text-muted">Sin acciones</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Modal de acción dinámica -->
<div class="modal fade" id="modalAccion" tabindex="-1" aria-labelledby="modalAccionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAccionLabel">Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalAccionBody">
                <!-- Aquí se cargará el formulario dinámicamente -->
            </div>
        </div>
    </div>
</div>
<!-- Modal para agregar ubicación global (solo uno por página) -->
<div class="modal fade" id="modalUbicacionGlobal" tabindex="-1" aria-labelledby="modalUbicacionGlobalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUbicacionGlobalLabel">Agregar Nueva Ubicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formUbicacionGlobal" novalidate>
                    <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="nombre_ubicacion" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Tipo</label><select name="tipo_ubicacion" class="form-select"><option value="">Seleccione...</option><option value="PC">PC</option><option value="OFICINA">OFICINA</option><option value="BODEGA">BODEGA</option></select></div>
                    <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
                    <div id="msgUbicacionGlobal" class="text-danger small mb-2"></div>
                    <button type="button" class="btn btn-primary" id="btnGuardarUbicacionGlobal">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- El script de Bootstrap debe ir al final del body para evitar conflictos de inicialización -->
<script>
// Forzar inicialización de todos los dropdowns de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
  var dropdownTriggerList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
  dropdownTriggerList.forEach(function (dropdownTriggerEl) {
    new bootstrap.Dropdown(dropdownTriggerEl);
  });

  // Acciones dinámicas
  let accionPendiente = null;
  let idDetallePendiente = null;
  const modalAccion = new bootstrap.Modal(document.getElementById('modalAccion'));
  const modalAccionBody = document.getElementById('modalAccionBody');

  function cargarFormularioAccion(tipo, id_detalle) {
      // AJAX para obtener datos del producto
      fetch('gestion_unificada_ajax.php?accion=formulario&tipo=' + encodeURIComponent(tipo) + '&id_detalle=' + encodeURIComponent(id_detalle))
          .then(r => r.text())
          .then(html => {
              modalAccionBody.innerHTML = html;
              modalAccion.show();
          });
  }

  document.querySelectorAll('.btn-salida').forEach(btn => {
      btn.addEventListener('click', function() {
          cargarFormularioAccion('salida', this.getAttribute('data-id'));
      });
  });
  document.querySelectorAll('.btn-baja').forEach(btn => {
      btn.addEventListener('click', function() {
          cargarFormularioAccion('baja', this.getAttribute('data-id'));
      });
  });
  document.querySelectorAll('.btn-devolucion').forEach(btn => {
      btn.addEventListener('click', function() {
          cargarFormularioAccion('devolucion', this.getAttribute('data-id'));
      });
  });
  document.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', function() {
          cargarFormularioAccion('editar', this.getAttribute('data-id'));
      });
  });
});
// Lógica para agregar ubicación desde el modal global
var selectUbicacionActivo = null;
document.addEventListener('click', function(e) {
    // Abrir modal ubicación
    if (e.target.classList.contains('btnMostrarModalUbicacionGlobal')) {
        var selectId = e.target.getAttribute('data-select-id');
        selectUbicacionActivo = document.getElementById(selectId);
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacionGlobal'));
        modal.show();
    }
    // Guardar cambios editar
    if (e.target.id === 'btnGuardarEditar') {
        var f = document.getElementById("formEditarUnidad");
        fetch(f.action, {
            method: "POST",
            body: new FormData(f)
        })
        .then(r => r.text())
        .then(t => {
            if (t.trim().toLowerCase() === 'ok') {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: 'Los cambios se guardaron correctamente.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => { location.reload(); });
                } else {
                    alert('Los cambios se guardaron correctamente.');
                    location.reload();
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: t
                    });
                } else {
                    alert('Error: ' + t);
                }
            }
        });
    }
});
document.getElementById('btnGuardarUbicacionGlobal').onclick = function() {
    var formUbicacion = document.getElementById('formUbicacionGlobal');
    var datos = new FormData(formUbicacion);
    var nombreNuevo = datos.get('nombre_ubicacion').trim().toLowerCase();
    var select = selectUbicacionActivo;
    if (!select) return;
    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].textContent.trim().toLowerCase() === nombreNuevo) {
            document.getElementById('msgUbicacion_' + select.id).textContent = 'Ya existe una ubicación con ese nombre.';
            return;
        }
    }
    if (!nombreNuevo || !datos.get('tipo_ubicacion')) {
        document.getElementById('msgUbicacion_' + select.id).textContent = 'Nombre y tipo son obligatorios.';
        return;
    }
    if (datos.has('tipo_ubicacion')) {
        datos.append('tipo', datos.get('tipo_ubicacion'));
    }
    fetch('ubicaciones/ajax_agregar.php', {
        method: 'POST',
        body: datos
    })
    .then(function(r) {
        if (!r.ok) throw new Error('No se pudo enviar la petición');
        return r.json();
    })
    .then(function(res) {
        if (res.success) {
            var opt = document.createElement('option');
            opt.value = res.id;
            opt.textContent = res.nombre;
            for (var i = 0; i < select.options.length - 1; i++) {
                if (select.options[i].value === res.id) {
                    select.remove(i);
                    break;
                }
            }
            select.appendChild(opt);
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacionGlobal'));
            modal.hide();
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(b) { b.remove(); });
            setTimeout(function() {
                select.value = res.id;
                select.dispatchEvent(new Event('change'));
            }, 100);
            formUbicacion.reset();
            document.getElementById('msgUbicacion_' + select.id).textContent = '';
        } else {
            document.getElementById('msgUbicacion_' + select.id).textContent = res.msg || 'Error.';
        }
    })
    .catch(function(e) {
        document.getElementById('msgUbicacion_' + select.id).textContent = 'Error de red o petición: ' + e.message;
    });
};
</script>
<!-- El script de Bootstrap solo debe estar en menu.php para evitar conflictos. -->
</body>
</html>
