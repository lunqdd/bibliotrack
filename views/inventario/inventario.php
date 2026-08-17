<?php
$pageTitle         = 'Inventario';
$pageCss           = 'inventario.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar en inventario...';
$activeNav         = 'inventario';
$pageJs            = ['js/inventario.js'];

$porcentaje = fn($valor) => $stats['total'] > 0 ? round($valor / $stats['total'] * 100) : 0;

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Gestión de Inventario</h1>
    <p>Control detallado de existencias, disponibilidad física y estado del acervo bibliográfico.</p>
  </div>
</div>

<div class="fila-stats">
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-archive stat-icono"></i></div>
    <p class="stat-label">Total Ejemplares</p>
    <p class="stat-valor" id="stat-total"><?= (int) $stats['total'] ?></p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-check-circle stat-icono"></i><span class="stat-trend"><?= $porcentaje($stats['disponibles']) ?>%</span></div>
    <p class="stat-label">Disponibles</p>
    <p class="stat-valor" id="stat-disponibles"><?= (int) $stats['disponibles'] ?></p>
    <div class="stat-barra"><div class="stat-barra-progreso" style="width:<?= $porcentaje($stats['disponibles']) ?>%;background-color:var(--exito)"></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-arrow-left-right stat-icono"></i><span class="stat-trend"><?= $porcentaje($stats['prestados']) ?>%</span></div>
    <p class="stat-label">En Préstamo</p>
    <p class="stat-valor" id="stat-prestados"><?= (int) $stats['prestados'] ?></p>
    <div class="stat-barra"><div class="stat-barra-progreso" style="width:<?= $porcentaje($stats['prestados']) ?>%;background-color:var(--alerta)"></div></div>
  </div>
  <div class="stat-card<?= $stats['enReparacion'] > 0 ? ' stat-error' : '' ?>">
    <div class="stat-header"><i class="bi bi-exclamation-diamond stat-icono"></i></div>
    <p class="stat-label">En Reparación</p>
    <p class="stat-valor" id="stat-reparacion"><?= (int) $stats['enReparacion'] ?></p>
  </div>
</div>

<div class="tabla-contenedor">
  <div class="tabla-filtros">
    <input type="search" id="buscar-inventario" placeholder="Buscar por ubicación...">
    <select id="filtro-estado-inv">
      <option value="">Estado de Sección</option>
      <option value="Completo">Completo</option>
      <option value="Parcial">Parcial</option>
      <option value="Agotado">Agotado</option>
    </select>
  </div>
  <div class="tabla-scroll">
    <table class="tabla">
      <thead><tr><th>Ubicación</th><th>Libro</th><th>ISBN</th><th>Total</th><th>En Estante</th><th>Estado de Sección</th><th>Acción</th></tr></thead>
      <tbody id="tabla-inventario">
        <?php if (empty($inventario)): ?>
          <tr><td colspan="7">No se encontraron existencias con ese criterio.</td></tr>
        <?php endif; ?>
        <?php foreach ($inventario as $item): ?>
          <?php $clase = $item['estado'] === 'Completo' ? 'badge-exito' : ($item['estado'] === 'Parcial' ? 'badge-alerta' : 'badge-error'); ?>
          <tr>
            <td>Pasillo <?= htmlspecialchars($item['pasillo']) ?> - Estante <?= htmlspecialchars($item['estante']) ?></td>
            <td><strong><?= htmlspecialchars($item['titulo']) ?></strong><br><span class="isbn-texto"><?= htmlspecialchars($item['autor']) ?></span></td>
            <td><span class="isbn-texto"><?= htmlspecialchars($item['isbn']) ?></span></td>
            <td class="texto-centro"><?= (int) $item['total'] ?></td>
            <td class="texto-centro"><?= (int) $item['en_estante'] ?></td>
            <td><span class="badge <?= $clase ?>"><i class="bi bi-circle-fill punto-estado"></i> <?= htmlspecialchars($item['estado']) ?></span></td>
            <td><button class="btn-icono" title="Editar" data-accion="editar" data-id="<?= (int) $item['libro_id'] ?>"><i class="bi bi-pencil-square"></i></button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-inv"><?= count($inventario) ?> registro<?= count($inventario) === 1 ? '' : 's' ?> encontrados</span>
    <div class="paginacion" id="paginacion-inventario"></div>
  </div>
</div>

<div class="modal-overlay" id="modal-inventario">
  <div class="modal">
    <div class="modal-header">
      <h2>Editar Ubicación</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-inventario"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div class="mensaje-error-servidor" id="mensaje-error-inventario" style="display:none"></div>
      <form id="form-inventario" novalidate>
        <input type="hidden" id="inventario-libro-id" value="">
        <div class="form-fila">
          <div class="form-grupo">
            <label for="inventario-pasillo">Pasillo</label>
            <input type="text" id="inventario-pasillo" placeholder="A">
          </div>
          <div class="form-grupo">
            <label for="inventario-estante">Estante</label>
            <input type="text" id="inventario-estante" placeholder="1">
          </div>
        </div>
        <div class="form-grupo">
          <label for="inventario-estanteria">En Estante</label>
          <input type="number" id="inventario-estanteria" min="0">
        </div>
        <p class="form-nota">El estado de la sección se calcula automáticamente a partir de los ejemplares en estante.</p>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-inventario">Cancelar</button>
      <button class="btn btn-primario" id="btn-guardar-inventario">Guardar Cambios</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
