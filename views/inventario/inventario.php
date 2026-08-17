<?php
$pageTitle         = 'Inventario';
$pageCss           = 'inventario.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar en inventario...';
$activeNav         = 'inventario';
$pageJs            = ['js/inventario.js'];

// El estado de la sección se calcula a partir de los ejemplares en estante
function calcularEstadoSeccion(array $seccion): string
{
    if ($seccion['en_estante'] <= 0) return 'Agotado';
    if ($seccion['en_estante'] < $seccion['totales']) return 'Parcial';
    return 'Completo';
}

function claseEstadoSeccion(string $estado): string
{
    if ($estado === 'Completo') return 'badge-exito';
    if ($estado === 'Parcial') return 'badge-alerta';
    return 'badge-error';
}

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
    <p class="stat-valor"><?= (int) $stats['totalEjemplares'] ?></p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-check-circle stat-icono"></i><span class="stat-trend"><?= (int) $stats['porcentajeDisponibles'] ?>%</span></div>
    <p class="stat-label">Disponibles</p>
    <p class="stat-valor"><?= (int) $stats['disponibles'] ?></p>
    <div class="stat-barra"><div class="stat-barra-progreso" style="width:<?= (int) $stats['porcentajeDisponibles'] ?>%;background-color:var(--exito)"></div></div>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-arrow-left-right stat-icono"></i><span class="stat-trend"><?= (int) $stats['porcentajePrestados'] ?>%</span></div>
    <p class="stat-label">En Préstamo</p>
    <p class="stat-valor"><?= (int) $stats['prestados'] ?></p>
    <div class="stat-barra"><div class="stat-barra-progreso" style="width:<?= (int) $stats['porcentajePrestados'] ?>%;background-color:var(--alerta)"></div></div>
  </div>
  <div class="stat-card stat-error">
    <div class="stat-header"><i class="bi bi-exclamation-diamond stat-icono"></i></div>
    <p class="stat-label">En Reparación</p>
    <p class="stat-valor"><?= (int) $stats['enReparacion'] ?></p>
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
  <div class="tabla-scroll"><table class="tabla">
    <thead><tr><th>Ubicación</th><th>Libro</th><th>ISBN</th><th>Total</th><th>En Estante</th><th>Estado de Sección</th><th>Acción</th></tr></thead>
    <tbody id="tabla-inventario">
      <?php foreach ($secciones as $seccion): $estado = calcularEstadoSeccion($seccion); ?>
        <tr data-texto="<?= htmlspecialchars(mb_strtolower('pasillo ' . $seccion['pasillo'] . ' ' . $seccion['estante'] . ' ' . $seccion['titulo'])) ?>" data-estado="<?= $estado ?>">
          <td>Pasillo <?= htmlspecialchars($seccion['pasillo']) ?> - Estante <?= htmlspecialchars($seccion['estante']) ?></td>
          <td><strong><?= htmlspecialchars($seccion['titulo']) ?></strong><br><span class="isbn-texto"><?= htmlspecialchars($seccion['autor']) ?></span></td>
          <td><span class="isbn-texto"><?= htmlspecialchars($seccion['isbn']) ?></span></td>
          <td class="texto-centro"><?= (int) $seccion['totales'] ?></td>
          <td class="texto-centro"><?= (int) $seccion['en_estante'] ?></td>
          <td><span class="badge <?= claseEstadoSeccion($estado) ?>"><i class="bi bi-circle-fill punto-estado"></i> <?= $estado ?></span></td>
          <td>
            <button class="btn-icono" title="Editar" data-accion="editar"
              data-libro-id="<?= (int) $seccion['libro_id'] ?>"
              data-ubicacion-id="<?= (int) $seccion['ubicacion_id'] ?>"
              data-pasillo="<?= htmlspecialchars($seccion['pasillo'], ENT_QUOTES) ?>"
              data-estante="<?= htmlspecialchars($seccion['estante'], ENT_QUOTES) ?>"
              data-en-estante="<?= (int) $seccion['en_estante'] ?>"
            ><i class="bi bi-pencil-square"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-inv">Mostrando <?= count($secciones) ?> de <?= count($secciones) ?> secciones</span>
  </div>
</div>

<div class="modal-overlay" id="modal-inventario">
  <div class="modal">
    <div class="modal-header">
      <h2>Editar Ubicación</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-inventario"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="form-inventario" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-inventario" style="display:none"></div>
        <input type="hidden" id="inventario-libro-id" name="libro_id">
        <input type="hidden" id="inventario-ubicacion-id" name="ubicacion_id">
        <div class="form-fila">
          <div class="form-grupo">
            <label for="inventario-pasillo">Pasillo</label>
            <input type="text" id="inventario-pasillo" name="pasillo" placeholder="A">
          </div>
          <div class="form-grupo">
            <label for="inventario-estante">Estante</label>
            <input type="text" id="inventario-estante" name="estante" placeholder="1">
          </div>
        </div>
        <div class="form-grupo">
          <label for="inventario-estanteria">En Estante</label>
          <input type="number" id="inventario-estanteria" name="en_estante" min="0">
        </div>
        <p class="form-nota">El estado de la sección se calcula automáticamente a partir de los ejemplares en estante.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-inventario">Cancelar</button>
        <button type="submit" class="btn btn-primario">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
