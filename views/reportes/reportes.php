<?php
$pageTitle         = 'Reportes y Estadísticas';
$pageCss           = 'reportes.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar reportes o libros...';
$activeNav         = 'reportes';

$maxPrestados = max(1, max(array_column($flujoMensual, 'prestados')));
$maxDevueltos = max(1, max(array_column($flujoMensual, 'devueltos')));
$maxFlujo = max($maxPrestados, $maxDevueltos);

$maxLibrosPrestados = max(1, ...array_column($librosMasPrestados, 'prestamos') ?: [1]);
$maxUsuariosActivos = max(1, ...array_column($usuariosMasActivos, 'prestamos') ?: [1]);

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Reportes</h1>
    <p>Consulta datos de circulación, usuarios e inventario.</p>
  </div>
</div>

<div class="fila-stats">
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-book stat-icono"></i></div>
    <p class="stat-label">Total Préstamos</p>
    <p class="stat-valor"><?= (int) $stats['totalPrestamos'] ?></p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-graph-up stat-icono"></i></div>
    <p class="stat-label">Tasa de Retorno</p>
    <p class="stat-valor"><?= (int) $stats['tasaRetorno'] ?>%</p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-people stat-icono"></i></div>
    <p class="stat-label">Usuarios Registrados</p>
    <p class="stat-valor"><?= (int) $stats['usuariosRegistrados'] ?></p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-collection stat-icono"></i></div>
    <p class="stat-label">Ejemplares en Circulación</p>
    <p class="stat-valor"><?= (int) $stats['ejemplaresCirculacion'] ?></p>
  </div>
</div>

<div class="reportes-grid">
  <div class="card">
    <h3>Movimiento Mensual de Libros</h3>
    <div class="leyenda-grafico">
      <span class="leyenda-item"><span class="leyenda-color leyenda-prestados"></span> Prestados</span>
      <span class="leyenda-item"><span class="leyenda-color leyenda-devueltos"></span> Devueltos</span>
    </div>
    <div class="grafico-barras-doble">
      <?php foreach ($flujoMensual as $mes): ?>
        <div class="barra-doble-grupo">
          <div class="barra-doble-par">
            <div class="barra-doble" style="height:<?= $mes['prestados'] === 0 ? 0 : max(8, round(($mes['prestados'] / $maxFlujo) * 155)) ?>px;background-color:var(--primary)"></div>
            <div class="barra-doble" style="height:<?= $mes['devueltos'] === 0 ? 0 : max(8, round(($mes['devueltos'] / $maxFlujo) * 155)) ?>px;background-color:var(--surface-tint)"></div>
          </div>
          <span class="barra-doble-label"><?= htmlspecialchars($mes['mes']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <h3>Libros Más Prestados</h3>
    <div>
      <?php if (empty($librosMasPrestados)): ?>
        <p>Todavía no hay préstamos registrados.</p>
      <?php endif; ?>
      <?php foreach ($librosMasPrestados as $libro): ?>
        <div class="categoria-item">
          <span class="categoria-nombre"><?= htmlspecialchars($libro['nombre']) ?></span>
          <div class="categoria-barra"><div class="categoria-barra-relleno" style="width:<?= round(($libro['prestamos'] / $maxLibrosPrestados) * 100) ?>%"></div></div>
          <span class="categoria-porcentaje"><?= (int) $libro['prestamos'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="reportes-grid mt-lg">
  <div class="card">
    <h3>Usuarios Más Activos</h3>
    <div>
      <?php foreach ($usuariosMasActivos as $usuario): ?>
        <div class="categoria-item">
          <span class="categoria-nombre"><?= htmlspecialchars($usuario['nombre']) ?></span>
          <div class="categoria-barra"><div class="categoria-barra-relleno" style="width:<?= round(($usuario['prestamos'] / $maxUsuariosActivos) * 100) ?>%"></div></div>
          <span class="categoria-porcentaje"><?= (int) $usuario['prestamos'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <h3>Estado General del Inventario</h3>
    <div>
      <div class="reporte-item">
        <div class="reporte-info"><strong>Completo</strong></div>
        <span class="badge badge-exito"><?= (int) $estadoInventario['completo'] ?> secciones</span>
      </div>
      <div class="reporte-item">
        <div class="reporte-info"><strong>Parcial</strong></div>
        <span class="badge badge-alerta"><?= (int) $estadoInventario['parcial'] ?> secciones</span>
      </div>
      <div class="reporte-item">
        <div class="reporte-info"><strong>Agotado</strong></div>
        <span class="badge badge-error"><?= (int) $estadoInventario['agotado'] ?> secciones</span>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
