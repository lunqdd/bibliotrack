<?php
$pageTitle         = 'Mi Portal';
$pageCss           = 'lector.css';
$topbarTitulo      = 'Panel del Lector';
$searchPlaceholder = 'Buscar en el catálogo...';
$activeNav         = 'lector';
$pageJs            = ['js/lector.js'];

$primerNombre = explode(' ', trim($_SESSION['usuario']['nombre_completo']))[0];
$hora   = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');

$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaTexto = $diasSemana[(int) date('w')] . ', ' . (int) date('j') . ' de ' . $meses[(int) date('n') - 1] . ' ' . date('Y');

$proximaEntregaTexto = 'Sin pendientes';
if ($stats['proximaEntrega']) {
    $dias = (int) (new DateTime())->diff(new DateTime($stats['proximaEntrega']))->format('%r%a');
    $proximaEntregaTexto = $dias >= 0 ? $dias . ' día' . ($dias === 1 ? '' : 's') : 'Vencido';
}

$tarjetas = [
    ['icono' => 'bi-book', 'label' => 'Libros en Casa', 'valor' => $stats['librosEnCasa'], 'badge' => 'Actual', 'badgeTipo' => 'info'],
    ['icono' => 'bi-calendar-event', 'label' => 'Próxima Entrega', 'valor' => $proximaEntregaTexto],
    ['icono' => 'bi-journal-text', 'label' => 'Préstamos Totales', 'valor' => $stats['totalPrestamos']],
    ['icono' => 'bi-journal-check', 'label' => 'Devoluciones Este Año', 'valor' => $stats['devolucionesAnio'], 'badge' => date('Y'), 'badgeTipo' => 'info', 'destacado' => true],
];

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1><?= htmlspecialchars($saludo) ?> de nuevo, <?= htmlspecialchars($primerNombre) ?></h1>
    <p><?= htmlspecialchars($fechaTexto) ?></p>
  </div>
  <div class="pagina-acciones">
    <button class="btn btn-outline" id="btn-nuevo-prestamo"><i class="bi bi-plus-lg"></i> Solicitar Préstamo</button>
  </div>
</div>

<div class="fila-stats">
  <?php foreach ($tarjetas as $tarjeta): ?>
    <div class="stat-card<?= !empty($tarjeta['destacado']) ? ' stat-destacado' : '' ?>">
      <div class="stat-header">
        <i class="bi <?= htmlspecialchars($tarjeta['icono']) ?> stat-icono"></i>
        <?php if (!empty($tarjeta['badge'])): ?>
          <span class="badge badge-<?= htmlspecialchars($tarjeta['badgeTipo']) ?>"><?= htmlspecialchars((string) $tarjeta['badge']) ?></span>
        <?php endif; ?>
      </div>
      <p class="stat-label"><?= htmlspecialchars($tarjeta['label']) ?></p>
      <p class="stat-valor"><?= htmlspecialchars((string) $tarjeta['valor']) ?></p>
    </div>
  <?php endforeach; ?>
</div>

<div class="lector-grid">
  <div class="card">
    <div class="flex-between mb-md">
      <h3>Mi Actividad</h3>
      <a href="index.php?controller=lector&action=misPrestamos" class="ver-todo">Ver todo el historial <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="tabla-scroll">
      <table class="tabla tabla-compacta">
        <thead><tr><th>Libro</th><th>Vencimiento</th><th>Estado</th></tr></thead>
        <tbody>
          <?php if (empty($prestamosActivos)): ?>
            <tr><td colspan="3">No tienes préstamos activos.</td></tr>
          <?php endif; ?>
          <?php foreach ($prestamosActivos as $prestamo):
            $inicio = new DateTime($prestamo['fecha_prestamo']);
            $vence  = new DateTime($prestamo['fecha_devolucion_programada']);
            $hoy    = new DateTime();
            $totalDias = max(1, $inicio->diff($vence)->days);
            $transcurridos = min($totalDias, max(0, $inicio->diff($hoy)->days));
            $progreso = (int) round(($transcurridos / $totalDias) * 100);
            $diasRestantes = (int) $hoy->diff($vence)->format('%r%a');
            $venceHoy = $diasRestantes <= 2;
          ?>
            <tr>
              <td><div><strong><?= htmlspecialchars($prestamo['titulo']) ?></strong><br><span class="isbn-texto"><?= htmlspecialchars($prestamo['autor']) ?></span></div></td>
              <td><?= htmlspecialchars($vence->format('d M, Y')) ?>
                <div class="barra-progreso-prestamo"><div class="barra-progreso-relleno <?= $venceHoy ? 'barra-error' : 'barra-exito' ?>" style="width:<?= $progreso ?>%"></div></div>
              </td>
              <td><span class="badge <?= $venceHoy ? 'badge-error' : 'badge-exito' ?>"><?= $venceHoy ? 'Vence pronto' : 'En tiempo' ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3>Otros Títulos Disponibles</h3>
    <?php if (empty($recomendados)): ?>
      <p>No hay más títulos disponibles en este momento.</p>
    <?php endif; ?>
    <?php foreach ($recomendados as $libro): ?>
      <div class="recomendado-item">
        <div class="recomendado-portada"><?= htmlspecialchars(mb_strtoupper(mb_substr($libro['titulo'], 0, 2))) ?></div>
        <div class="recomendado-info">
          <h4><?= htmlspecialchars($libro['titulo']) ?></h4>
          <p><?= htmlspecialchars($libro['autor']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Modal Solicitar Préstamo -->
<div class="modal-overlay" id="modal-prestamo">
  <div class="modal">
    <div class="modal-header">
      <h2>Solicitar Préstamo</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-prestamo"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="index.php?controller=lector&action=solicitar" id="form-prestamo" novalidate>
      <div class="modal-body">
        <div class="mensaje-exito" id="mensaje-exito-prestamo" style="display:none"></div>
        <div class="mensaje-error-servidor" id="mensaje-error-prestamo" style="display:none"></div>
        <div class="form-grupo">
          <label for="prestamo-libro">Libro</label>
          <select id="prestamo-libro" name="libro_id" required>
            <option value="">Seleccionar libro</option>
            <?php foreach ($librosDisponibles as $libro): ?>
              <option value="<?= (int) $libro['libro_id'] ?>"><?= htmlspecialchars($libro['titulo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label for="prestamo-fecha">Fecha de retiro</label>
          <input type="date" id="prestamo-fecha" name="fecha" required>
        </div>
        <div class="form-grupo">
          <label for="prestamo-notas">Notas</label>
          <textarea id="prestamo-notas" name="notas" rows="3" placeholder="Escribe una nota si la necesitas"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-prestamo">Cancelar</button>
        <button type="submit" class="btn btn-primario">Solicitar Préstamo</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
