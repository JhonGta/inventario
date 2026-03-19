<?php
// menu.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Inventario</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/Inventario/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/Inventario/categorias/listar.php">Categorías</a></li>
        <li class="nav-item"><a class="nav-link" href="/Inventario/productos/listar.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="/Inventario/ubicaciones/listar.php">Ubicaciones</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="movimientosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Movimientos</a>
          <ul class="dropdown-menu" aria-labelledby="movimientosDropdown">
            <li><a class="dropdown-item" href="/Inventario/movimientos/entrada.php">Entrada</a></li>
            <li><a class="dropdown-item" href="/Inventario/movimientos/salida.php">Salida</a></li>
            <li><a class="dropdown-item" href="/Inventario/movimientos/devolucion.php">Devolución</a></li>
            <li><a class="dropdown-item" href="/Inventario/movimientos/baja.php">Baja</a></li>
            <li><a class="dropdown-item" href="/Inventario/movimientos/historial.php">Historial</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="/Inventario/gestion_unificada.php">Gestión Unificada</a></li>
      </ul>
      <span class="navbar-text me-3">👤 <?= htmlspecialchars($_SESSION['nombre']) ?></span>
      <a href="/Inventario/logout.php" class="btn btn-outline-light">Cerrar sesión</a>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
