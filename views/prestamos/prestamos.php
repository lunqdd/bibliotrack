<?php
$pageTitle         = 'Préstamos y Devoluciones';
$pageCss           = 'prestamos.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar préstamos, usuarios o títulos...';
$activeNav         = 'prestamos';
$pageJs            = ['js/prestamos.js'];

// Un préstamo vencido es uno sin devolución cuya fecha programada ya pasó
function estadoPrestamo(array $p): string
{
    if ($p['fecha_devolucion']) return 'DEVUELTO';
    return strtotime($p['fecha_devolucion_programada']) < strtotime('today') ? 'VENCIDO' : 'EN CURSO';
}

function claseBadgePrestamo(string $estado): string
{
    if ($estado === 'DEVUELTO') return 'badge-exito';
    if ($estado === 'EN CURSO') return 'badge-alerta';
    return 'badge-error';
}

function etiquetaEstadoEjemplar(?string $valor): string
{
    if ($valor === 'buen_estado') return 'Buen estado';
    if ($valor === 'dano_menor') return 'Daño menor';
    if ($valor === 'requiere_reparacion') return 'Requiere reparación';
    return '';
}

function inicialesNombre(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre));
    $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1));
    if (isset($partes[1])) {
        $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));
    }
    return $iniciales;
}

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Préstamos y Devoluciones</h1>
    <p>Registra préstamos, devoluciones y revisa el historial de ejemplares.</p>
  </div>
  <div class="pagina-acciones">
    <button class="btn btn-outline" id="btn-abrir-devolucion"><i class="bi bi-arrow-return-left"></i> Registrar Devolución</button>
    <button class="btn btn-oscuro" id="btn-abrir-prestamo"><i class="bi bi-plus-circle"></i> Registrar Nuevo Préstamo</button>
  </div>
</div>

<div class="fila-stats">
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-book stat-icono"></i></div>
    <p class="stat-label">Préstamos Activos</p>
    <p class="stat-valor"><?= (int) $stats['activos'] ?></p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-arrow-return-left stat-icono"></i></div>
    <p class="stat-label">Devoluciones Registradas</p>
    <p class="stat-valor"><?= (int) $stats['devoluciones'] ?></p>
  </div>
  <div class="stat-card stat-error">
    <div class="stat-header"><i class="bi bi-exclamation-triangle stat-icono"></i></div>
    <p class="stat-label">Libros Vencidos</p>
    <p class="stat-valor"><?= (int) $stats['vencidos'] ?></p>
  </div>
  <div class="stat-card">
    <div class="stat-header"><i class="bi bi-graph-up stat-icono"></i></div>
    <p class="stat-label">Tasa de Retorno</p>
    <p class="stat-valor"><?= (int) $stats['tasaRetorno'] ?>%</p>
  </div>
</div>

<div class="tabla-contenedor">
  <div class="tabla-header">
    <h2>Historial de Préstamos</h2>
    <div class="flex gap-sm">
      <select id="filtro-estado-prestamo">
        <option value="">Filtrar por Estado</option>
        <option value="DEVUELTO">DEVUELTO</option>
        <option value="EN CURSO">EN CURSO</option>
        <option value="VENCIDO">VENCIDO</option>
      </select>
    </div>
  </div>
  <div class="tabla-scroll"><table class="tabla">
    <thead><tr><th>Usuario</th><th>Libro</th><th>Fecha Préstamo</th><th>Fecha Devolución</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody id="tabla-prestamos">
      <?php foreach ($prestamos as $p): $estado = estadoPrestamo($p); ?>
        <tr data-estado="<?= $estado ?>">
          <td><div class="celda-usuario"><div class="avatar-tabla"><?= htmlspecialchars(inicialesNombre($p['nombre_completo'])) ?></div><div><span class="nombre-usuario"><?= htmlspecialchars($p['nombre_completo']) ?></span><br><span class="id-usuario">ID: <?= htmlspecialchars($p['usuario_codigo']) ?></span></div></div></td>
          <td><div><?= htmlspecialchars($p['titulo']) ?><br><span class="isbn-texto">ISBN: <?= htmlspecialchars($p['isbn']) ?></span></div></td>
          <td><?= htmlspecialchars((new DateTime($p['fecha_prestamo']))->format('d M Y')) ?></td>
          <td><?php if ($p['fecha_devolucion']): ?>
            <?= htmlspecialchars((new DateTime($p['fecha_devolucion']))->format('d M Y')) ?>
          <?php else: ?>
            <span class="isbn-texto">Programada: <?= htmlspecialchars((new DateTime($p['fecha_devolucion_programada']))->format('d M Y')) ?></span>
          <?php endif; ?></td>
          <td><span class="badge <?= claseBadgePrestamo($estado) ?>"><?= $estado ?></span></td>
          <td class="acciones-celda">
            <button class="btn-icono" title="Ver detalle" data-accion="ver"
              data-nombre="<?= htmlspecialchars($p['nombre_completo'], ENT_QUOTES) ?>"
              data-codigo="<?= htmlspecialchars($p['usuario_codigo'], ENT_QUOTES) ?>"
              data-titulo="<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>"
              data-isbn="<?= htmlspecialchars($p['isbn'], ENT_QUOTES) ?>"
              data-fecha-prestamo="<?= htmlspecialchars((new DateTime($p['fecha_prestamo']))->format('d M Y'), ENT_QUOTES) ?>"
              data-fecha-programada="<?= htmlspecialchars((new DateTime($p['fecha_devolucion_programada']))->format('d M Y'), ENT_QUOTES) ?>"
              data-fecha-real="<?= $p['fecha_devolucion'] ? htmlspecialchars((new DateTime($p['fecha_devolucion']))->format('d M Y'), ENT_QUOTES) : '' ?>"
              data-estado="<?= $estado ?>"
              data-estado-ejemplar="<?= htmlspecialchars(etiquetaEstadoEjemplar($p['estado_ejemplar']), ENT_QUOTES) ?>"
              data-notas="<?= htmlspecialchars($p['notas_devolucion'] ?? '', ENT_QUOTES) ?>"
            ><i class="bi bi-eye"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-prestamos">Mostrando <?= count($prestamos) ?> de <?= count($prestamos) ?> registros</span>
  </div>
</div>

<!-- Modal Registrar Préstamo -->
<div class="modal-overlay" id="modal-prestamo">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modal-prestamo-titulo">Registrar Nuevo Préstamo</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-prestamo"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="form-prestamo" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-prestamo" style="display:none"></div>
        <div class="form-grupo">
          <label for="prestamo-usuario">Usuario</label>
          <select id="prestamo-usuario" name="usuario_id">
            <option value="">Seleccionar usuario</option>
            <?php foreach ($usuarios as $u): ?>
              <option value="<?= (int) $u['usuario_id'] ?>"><?= htmlspecialchars($u['nombre_completo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label for="prestamo-libro">Libro</label>
          <select id="prestamo-libro" name="libro_id">
            <option value="">Seleccionar libro</option>
            <?php foreach ($libros as $l): ?>
              <option value="<?= (int) $l['libro_id'] ?>"><?= htmlspecialchars($l['titulo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <p class="form-nota">Se asigna automáticamente el primer ejemplar disponible del libro elegido, con 14 días de plazo.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-prestamo">Cancelar</button>
        <button type="submit" class="btn btn-primario">Registrar Préstamo</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Registrar Devolución -->
<div class="modal-overlay" id="modal-devolucion">
  <div class="modal">
    <div class="modal-header">
      <h2>Registrar Devolución</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-devolucion"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="form-devolucion" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-devolucion" style="display:none"></div>
        <div class="form-grupo">
          <label for="devolucion-prestamo">Préstamo Activo</label>
          <select id="devolucion-prestamo" name="prestamo_id">
            <option value="">Seleccionar préstamo a devolver</option>
            <?php foreach ($prestamos as $p): if (estadoPrestamo($p) === 'DEVUELTO') continue; ?>
              <option value="<?= (int) $p['prestamo_id'] ?>"><?= htmlspecialchars($p['nombre_completo']) ?> — <?= htmlspecialchars($p['titulo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label for="devolucion-estado">Estado del Ejemplar</label>
          <select id="devolucion-estado" name="estado_ejemplar">
            <option value="">Seleccionar estado</option>
            <option value="buen_estado">Buen estado</option>
            <option value="dano_menor">Daño menor</option>
            <option value="requiere_reparacion">Requiere reparación</option>
          </select>
        </div>
        <div class="form-grupo">
          <label for="devolucion-notas">Notas</label>
          <textarea id="devolucion-notas" name="notas" rows="3" placeholder="Observaciones sobre la devolución..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-devolucion">Cancelar</button>
        <button type="submit" class="btn btn-primario">Confirmar Devolución</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Detalle Préstamo -->
<div class="modal-overlay" id="modal-detalle-prestamo">
  <div class="modal">
    <div class="modal-header">
      <h2>Detalle del Préstamo</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-detalle-prestamo"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="detalle-prestamo-contenido"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-detalle-prestamo">Cerrar</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
