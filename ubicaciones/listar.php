<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header('Location: ../login.php'); exit; }
require_once '../config/conexion.php';
$porPagina = isset($_GET['porPagina']) ? (int)$_GET['porPagina'] : 10;
$porPagina = in_array($porPagina, [5,10,15,20]) ? $porPagina : 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtroTipo = isset($_GET['filtroTipo']) ? trim($_GET['filtroTipo']) : '';
$where = "estado = 'ACTIVO'";
if ($busqueda) {
    $where .= " AND nombre_ubicacion LIKE :busqueda";
}
if ($filtroTipo) {
    $where .= " AND tipo = :tipo";
}
$sqlTotal = "SELECT COUNT(*) FROM ubicaciones WHERE $where";
$stmtTotal = $pdo->prepare($sqlTotal);
if ($busqueda) $stmtTotal->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
if ($filtroTipo) $stmtTotal->bindValue(':tipo', $filtroTipo, PDO::PARAM_STR);
$stmtTotal->execute();
$total = $stmtTotal->fetchColumn();
$totalPaginas = ceil($total / $porPagina);
$offset = ($pagina - 1) * $porPagina;
$sql = "SELECT * FROM ubicaciones WHERE $where ORDER BY nombre_ubicacion LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if ($busqueda) $stmt->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
if ($filtroTipo) $stmt->bindValue(':tipo', $filtroTipo, PDO::PARAM_STR);
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$ubicaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ubicaciones | Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../menu.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Ubicaciones</h3>
        <a href="crear.php" class="btn btn-primary">Nueva Ubicación</a>
    </div>
    <form method="get" class="mb-2 row g-2 align-items-center">
        <div class="col-auto">
            <label>Mostrar:
                <select name="porPagina" class="form-select d-inline w-auto" onchange="this.form.submit()">
                    <option value="5" <?= $porPagina==5?'selected':'' ?>>5</option>
                    <option value="10" <?= $porPagina==10?'selected':'' ?>>10</option>
                    <option value="15" <?= $porPagina==15?'selected':'' ?>>15</option>
                    <option value="20" <?= $porPagina==20?'selected':'' ?>>20</option>
                </select>
            </label>
        </div>
        <div class="col-auto">
            <input type="text" name="busqueda" class="form-control" placeholder="Buscar nombre..." value="<?= htmlspecialchars($busqueda) ?>">
        </div>
        <div class="col-auto">
            <select name="filtroTipo" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los tipos</option>
                <option value="PC" <?= $filtroTipo=='PC'?'selected':'' ?>>PC</option>
                <option value="OFICINA" <?= $filtroTipo=='OFICINA'?'selected':'' ?>>OFICINA</option>
                <option value="BODEGA" <?= $filtroTipo=='BODEGA'?'selected':'' ?>>BODEGA</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
        </div>
    </form>
    <table class="table table-bordered table-hover">
        <thead>
            <tr><th>Nombre</th><th>Tipo</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($ubicaciones as $ubi): ?>
            <tr>
                <td><?= htmlspecialchars($ubi['nombre_ubicacion']) ?></td>
                <td><?= $ubi['tipo'] ?></td>
                <td><?= htmlspecialchars($ubi['descripcion']) ?></td>
                <td>
                    <a href="editar.php?id=<?= $ubi['id_ubicacion'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="eliminar.php?id=<?= $ubi['id_ubicacion'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta ubicación?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalPaginas > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($i=1; $i<=$totalPaginas; $i++): ?>
                <li class="page-item <?= $i==$pagina?'active':'' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>&porPagina=<?= $porPagina ?>&busqueda=<?= urlencode($busqueda) ?>&filtroTipo=<?= urlencode($filtroTipo) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
</body>
</html>
