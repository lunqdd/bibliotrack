<?php
$pageTitle         = 'Mis Préstamos';
$pageCss           = 'mis-prestamos.css';
$topbarTitulo      = 'Mis Préstamos';
$searchPlaceholder = 'Buscar en mis préstamos...';
$activeNav         = 'misPrestamos';
$pageJs            = ['js/mis-prestamos.js'];

$hoy = new DateTime();

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div><h1>Mis Préstamos</h1><p>Tus préstamos activos y el historial completo de movimientos.</p></div>
</div>

<div class="tabs-lector" role="tablist">
  <button class="tab-lector activo" id="tab-activos" data-tab="activos" role="tab" aria-selected="true">Activos</button>
  <button class="tab-lector" id="tab-historial" data-tab="historial" role="tab" aria-selected="false">Historial</button>
</div>

<div class="tabla-contenedor" id="panel-activos" role="tabpanel">
  <div class="tabla-scroll">
    <table class="tabla">
      <thead><tr><th>Libro</th><th>Fecha Préstamo</th><th>Vencimiento</th><th>Estado</th></tr></thead>
      <tbody>
        <?php if (empty($activos)): ?>
          <tr><td colspan="4">No tienes préstamos activos.</td></tr>
        <?php endif; ?>
        <?php foreach ($activos as $prestamo):
          $vence = new DateTime($prestamo['fecha_devolucion_programada']);
          $diasRestantes = (int) $hoy->diff($vence)->format('%r%a');
          $vencePronto = $diasRestantes <= 2;
        ?>
          <tr>
            <td><div class="libro-info"><span class="libro-titulo"><?= htmlspecialchars($prestamo['titulo']) ?></span><span class="libro-autor"><?= htmlspecialchars($prestamo['autor']) ?></span></div></td>
            <td><?= htmlspecialchars((new DateTime($prestamo['fecha_prestamo']))->format('d M Y')) ?></td>
            <td><?= htmlspecialchars($vence->format('d M Y')) ?></td>
            <td><span class="badge <?= $vencePronto ? 'badge-error' : 'badge-exito' ?>"><?= $vencePronto ? 'Vence pronto' : 'En tiempo' ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="tabla-contenedor oculto" id="panel-historial" role="tabpanel">
  <div class="tabla-scroll">
    <table class="tabla">
      <thead><tr><th>Libro</th><th>Fecha Préstamo</th><th>Fecha Devolución</th><th>Estado</th></tr></thead>
      <tbody>
        <?php if (empty($historial)): ?>
          <tr><td colspan="4">Todavía no tienes préstamos devueltos.</td></tr>
        <?php endif; ?>
        <?php foreach ($historial as $prestamo): ?>
          <tr>
            <td><div class="libro-info"><span class="libro-titulo"><?= htmlspecialchars($prestamo['titulo']) ?></span><span class="libro-autor"><?= htmlspecialchars($prestamo['autor']) ?></span></div></td>
            <td><?= htmlspecialchars((new DateTime($prestamo['fecha_prestamo']))->format('d M Y')) ?></td>
            <td><?= htmlspecialchars((new DateTime($prestamo['fecha_devolucion']))->format('d M Y')) ?></td>
            <td><span class="badge badge-exito">DEVUELTO</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
