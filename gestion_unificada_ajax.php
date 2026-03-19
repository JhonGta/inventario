<?php
// gestion_unificada_ajax.php
session_start();
if (!isset($_SESSION['id_admin'])) { http_response_code(403); exit('No autorizado'); }
require_once __DIR__ . '/config/conexion.php';

$accion = $_GET['accion'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$id_detalle = $_GET['id_detalle'] ?? '';

if ($accion === 'formulario' && $id_detalle && in_array($tipo, ['salida','baja','devolucion','editar'])) {
    // Obtener datos del detalle y producto
    $stmt = $pdo->prepare('SELECT pd.*, p.nombre AS producto, p.id_producto, c.nombre AS categoria FROM productos_detalle pd JOIN productos p ON pd.id_producto = p.id_producto JOIN categorias c ON p.id_categoria = c.id_categoria WHERE pd.id_detalle = ?');
    $stmt->execute([$id_detalle]);
    $d = $stmt->fetch();
    if (!$d) exit('No encontrado');
    // Formulario según tipo
    if ($tipo === 'salida') {
        // Ubicaciones
        $ubicaciones = $pdo->query('SELECT * FROM ubicaciones WHERE estado = "ACTIVO" ORDER BY nombre_ubicacion')->fetchAll();
        echo '<form method="post" action="movimientos/salida.php" id="formAccionSalida">';
        echo '<input type="hidden" name="id_producto" value="'.htmlspecialchars($d['id_producto']).'">';
        echo '<input type="hidden" name="id_detalle[]" value="'.htmlspecialchars($d['id_detalle']).'">';
        echo '<h6>Salida de: <b>'.htmlspecialchars($d['producto']).' ('.htmlspecialchars($d['marca']).' - '.htmlspecialchars($d['serie']).')</b></h6>';
        echo '<div class="mb-2"><label>Responsable</label><input type="text" name="responsable" class="form-control" required></div>';
        $uniqSalida = uniqid('ubi_salida_');
        echo '<div class="mb-2"><label>Ubicación (opcional)</label>';
        echo '<div class="input-group">';
        echo '<select name="id_ubicacion" id="'.$uniqSalida.'" class="form-select"><option value="">Sin ubicación</option>';
        foreach ($ubicaciones as $u) {
            echo '<option value="'.$u['id_ubicacion'].'">'.htmlspecialchars($u['nombre_ubicacion']).'</option>';
        }
        echo '</select>';
        echo '<button type="button" class="btn btn-outline-primary btnMostrarModalUbicacionGlobal" data-select-id="'.$uniqSalida.'">+ Agregar Ubicación</button>';
        echo '</div>';
        echo '</div>';
        echo '<div class="mb-2"><label>Observación</label><textarea name="observacion" class="form-control"></textarea></div>';
        echo '<button type="submit" class="btn btn-danger">Confirmar Salida</button>';
        echo '</form>';
        echo '<script>document.getElementById("formAccionSalida").onsubmit=function(e){e.preventDefault();var f=this;fetch(f.action,{method:"POST",body:new FormData(f)}).then(r=>r.text()).then(t=>{location.reload();});};</script>';
    } elseif ($tipo === 'baja') {
        echo '<form method="post" action="movimientos/baja.php" id="formAccionBaja">';
        echo '<input type="hidden" name="id_producto" value="'.htmlspecialchars($d['id_producto']).'">';
        echo '<input type="hidden" name="id_detalle[]" value="'.htmlspecialchars($d['id_detalle']).'">';
        echo '<h6>Baja de: <b>'.htmlspecialchars($d['producto']).' ('.htmlspecialchars($d['marca']).' - '.htmlspecialchars($d['serie']).')</b></h6>';
        echo '<div class="mb-2"><label>Observación</label><textarea name="observacion" class="form-control"></textarea></div>';
        echo '<button type="submit" class="btn btn-secondary">Confirmar Baja</button>';
        echo '</form>';
        echo '<script>document.getElementById("formAccionBaja").onsubmit=function(e){e.preventDefault();var f=this;fetch(f.action,{method:"POST",body:new FormData(f)}).then(r=>r.text()).then(t=>{location.reload();});};</script>';
    } elseif ($tipo === 'devolucion') {
        echo '<form method="post" action="movimientos/devolucion.php" id="formAccionDevolucion">';
        echo '<input type="hidden" name="id_producto" value="'.htmlspecialchars($d['id_producto']).'">';
        echo '<input type="hidden" name="id_detalle[]" value="'.htmlspecialchars($d['id_detalle']).'">';
        echo '<h6>Devolución de: <b>'.htmlspecialchars($d['producto']).' ('.htmlspecialchars($d['marca']).' - '.htmlspecialchars($d['serie']).')</b></h6>';
        echo '<button type="submit" class="btn btn-success">Confirmar Devolución</button>';
        echo '</form>';
        echo '<script>document.getElementById("formAccionDevolucion").onsubmit=function(e){e.preventDefault();var f=this;fetch(f.action,{method:"POST",body:new FormData(f)}).then(r=>r.text()).then(t=>{location.reload();});};</script>';
    } elseif ($tipo === 'editar') {
        $ubicaciones = $pdo->query('SELECT * FROM ubicaciones WHERE estado = "ACTIVO" ORDER BY nombre_ubicacion')->fetchAll();
        // Obtener último movimiento para responsable, ubicación y observación
        $stmtMov = $pdo->prepare('SELECT m.responsable, m.id_ubicacion, m.observacion FROM movimiento_detalle md JOIN movimientos m ON md.id_movimiento = m.id_movimiento WHERE md.id_detalle = ? ORDER BY m.fecha DESC LIMIT 1');
        $stmtMov->execute([$d['id_detalle']]);
        $mov = $stmtMov->fetch();
        $responsable = $mov['responsable'] ?? '';
        $id_ubicacion = $mov['id_ubicacion'] ?? '';
        $observacion = $mov['observacion'] ?? '';
        echo '<form id="formEditarUnidad" action="gestion_unificada_editar.php" method="post">';
        echo '<input type="hidden" name="id_detalle" value="'.htmlspecialchars($d['id_detalle']).'">';
        echo '<div class="mb-2"><label>Responsable</label><input type="text" name="responsable" class="form-control" value="'.htmlspecialchars($responsable).'" required></div>';
        $uniq = uniqid('ubi_');
        echo '<div class="mb-2"><label>Ubicación</label>';
        echo '<div class="input-group">';
        echo '<select name="id_ubicacion" id="'.$uniq.'" class="form-select"><option value="">Sin ubicación</option>';
        foreach ($ubicaciones as $u) {
            $selected = ($id_ubicacion == $u['id_ubicacion']) ? 'selected' : '';
            echo '<option value="'.$u['id_ubicacion'].'" '.$selected.'>'.htmlspecialchars($u['nombre_ubicacion']).'</option>';
        }
        echo '</select>';
        echo '<button type="button" class="btn btn-outline-primary btnMostrarModalUbicacionGlobal" data-select-id="'.$uniq.'">+ Agregar Ubicación</button>';
        echo '</div>';
        echo '<div class="form-text">Si el producto se almacena o instala en una ubicación específica, selecciónala o agrégala.</div>';
        echo '</div>';
        // El modal ahora es global, generado en gestion_unificada.php
        echo '<div class="mb-2"><label>Observación</label><textarea name="observacion" class="form-control">'.htmlspecialchars($observacion).'</textarea></div>';
        echo '<button type="button" class="btn btn-primary" id="btnGuardarEditar">Guardar Cambios</button>';
        echo '</form>';
        ?>
        <script>
        // Variable global para el select activo
        var selectUbicacionActivo = null;
        // Mostrar el modal global al hacer click en el botón
        document.querySelectorAll('.btnMostrarModalUbicacionGlobal').forEach(function(btn) {
            btn.onclick = function() {
                var selectId = btn.getAttribute('data-select-id');
                selectUbicacionActivo = document.getElementById(selectId);
                var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUbicacionGlobal'));
                modal.show();
            };
        });
        // Lógica para guardar ubicación desde el modal global
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
        document.getElementById("btnGuardarEditar").onclick = function() {
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
        };
        </script>
        <?php
    }
    exit;
}
http_response_code(400);
echo 'Petición inválida';
