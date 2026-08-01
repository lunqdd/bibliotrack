<?php
$pageTitle        = 'Panel Principal';
$pageCss          = 'dashboard.css';
$topbarTitulo     = 'Sistema de Administración';
$searchPlaceholder = 'Buscar en el archivo...';
$activeNav        = 'dashboard';
$pageJs           = ['js/dashboard.js'];

$primerNombre = explode(' ', trim($_SESSION['usuario']['nombre_completo']))[0];
$hora   = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');

$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaTexto = $diasSemana[(int) date('w')] . ', ' . (int) date('j') . ' de ' . $meses[(int) date('n') - 1] . ' ' . date('Y');

$tarjetas = [
    ['icono' => 'bi-book', 'label' => 'Títulos Registrados', 'valor' => $stats['libros'], 'trend' => $stats['ejemplares'] . ' ejemplares'],
    ['icono' => 'bi-arrow-left-right', 'label' => 'Préstamos Activos', 'valor' => $stats['prestamos'], 'badge' => 'En circulación', 'badgeTipo' => 'info'],
    ['icono' => 'bi-exclamation-triangle', 'label' => 'Devoluciones Tardías', 'valor' => $stats['tardias'], 'tipo' => $stats['tardias'] > 0 ? 'error' : null],
    ['icono' => 'bi-people', 'label' => 'Usuarios Registrados', 'valor' => $stats['usuarios'], 'trend' => 'Todos activos'],
];

$acciones = [
    ['icono' => 'bi-book', 'texto' => 'Registrar Libro', 'destino' => 'libros.html#registrar-libro'],
    ['icono' => 'bi-arrow-left-right', 'texto' => 'Nuevo Préstamo', 'destino' => 'prestamos.html#nuevo-prestamo'],
    ['icono' => 'bi-person-plus', 'texto' => 'Registrar Usuario', 'destino' => 'usuarios.html#registrar-usuario'],
];

$badgesAccion = ['Préstamo' => 'alerta', 'Devolución' => 'exito'];

$maxTendencia = max(1, max(array_column($tendencia, 'valor')));

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1><?= htmlspecialchars($saludo) ?>, <?= htmlspecialchars($primerNombre) ?></h1>
    <p><?= htmlspecialchars($fechaTexto) ?></p>
  </div>
  <div class="pagina-acciones">
    <span class="badge badge-exito"><i class="bi bi-check-circle"></i> Sistema activo</span>
  </div>
</div>

<div class="fila-stats">
  <?php foreach ($tarjetas as $tarjeta): ?>
    <div class="stat-card<?= ($tarjeta['tipo'] ?? null) === 'error' ? ' stat-error' : '' ?>">
      <div class="stat-header">
        <i class="bi <?= htmlspecialchars($tarjeta['icono']) ?> stat-icono"></i>
        <?php if (!empty($tarjeta['trend'])): ?>
          <span class="stat-trend"><?= htmlspecialchars($tarjeta['trend']) ?></span>
        <?php elseif (!empty($tarjeta['badge'])): ?>
          <span class="badge badge-<?= htmlspecialchars($tarjeta['badgeTipo']) ?>"><?= htmlspecialchars($tarjeta['badge']) ?></span>
        <?php endif; ?>
      </div>
      <p class="stat-label"><?= htmlspecialchars($tarjeta['label']) ?></p>
      <p class="stat-valor"><?= htmlspecialchars((string) $tarjeta['valor']) ?></p>
    </div>
  <?php endforeach; ?>
</div>

<div class="dashboard-grid">
  <div>
    <div class="card mb-lg">
      <h3 class="seccion-titulo">Acciones Rápidas</h3>
      <div class="acciones-rapidas">
        <?php foreach ($acciones as $accion): ?>
          <a href="<?= htmlspecialchars($accion['destino']) ?>" class="accion-rapida">
            <i class="bi <?= htmlspecialchars($accion['icono']) ?>"></i> <?= htmlspecialchars($accion['texto']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
      <div class="flex-between mb-md">
        <h3 class="seccion-titulo">Actividad Reciente</h3>
        <button type="button" class="btn btn-outline btn-sm" id="btn-actualizar-actividad"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
      </div>
      <div class="tabla-scroll">
        <table class="tabla tabla-compacta">
          <thead><tr><th>Usuario</th><th>Libro</th><th>Acción</th><th>Fecha</th></tr></thead>
          <tbody id="tabla-actividad">
            <?php if (empty($actividad)): ?>
              <tr><td colspan="4">Sin movimientos recientes.</td></tr>
            <?php endif; ?>
            <?php foreach ($actividad as $mov): ?>
              <tr>
                <td><?= htmlspecialchars($mov['usuario']) ?></td>
                <td><em><?= htmlspecialchars($mov['libro']) ?></em></td>
                <td><span class="badge badge-<?= $badgesAccion[$mov['accion']] ?? 'info' ?>"><?= htmlspecialchars($mov['accion']) ?></span></td>
                <td><?= htmlspecialchars($mov['fecha']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <div class="card mb-lg">
      <h3 class="seccion-titulo">Tendencia de Préstamos</h3>
      <div class="grafico-barras">
        <?php foreach ($tendencia as $dia): ?>
          <div class="barra-grupo">
            <div class="barra" style="height:<?= (int) round(($dia['valor'] / $maxTendencia) * 110) ?>px; background-color: <?= $dia['hoy'] ? 'var(--surface-tint)' : 'var(--primary)' ?>;"></div>
            <?php if ($dia['hoy']): ?><span class="barra-tag">Hoy</span><?php endif; ?>
            <span class="barra-label"><?= htmlspecialchars($dia['dia']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
      <h3 class="seccion-titulo">Libros Más Solicitados</h3>
      <?php if (empty($librosPopulares)): ?>
        <p>Todavía no hay préstamos registrados.</p>
      <?php endif; ?>
      <?php foreach ($librosPopulares as $libro): ?>
        <div class="libro-popular">
          <div class="libro-popular-portada"><?= htmlspecialchars(mb_strtoupper(mb_substr($libro['titulo'], 0, 2))) ?></div>
          <div class="libro-popular-info">
            <h4><?= htmlspecialchars($libro['titulo']) ?></h4>
            <p><?= htmlspecialchars($libro['autor']) ?></p>
            <span class="prestamos-count"><i class="bi bi-star-fill"></i> <?= (int) $libro['prestamos'] ?> préstamos</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
