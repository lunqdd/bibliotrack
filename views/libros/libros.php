<?php
$pageTitle         = 'Gestión de Libros';
$pageCss           = 'libros.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar libro...';
$activeNav         = 'libros';
$pageJs            = ['js/libros.js'];

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Gestión de Libros</h1>
    <p>Administra el catálogo completo, existencias y disponibilidad de la biblioteca.</p>
  </div>
  <div class="pagina-acciones">
    <button type="button" class="btn btn-primario" id="btn-nuevo-libro"><i class="bi bi-plus-lg"></i> Registrar Nuevo Libro</button>
  </div>
</div>

<div class="fila-stats">
  <div class="stat-card">
    <p class="stat-label">Títulos Registrados</p>
    <p class="stat-valor" id="stat-titulos"><?= (int) $stats['titulos'] ?></p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Total Ejemplares</p>
    <p class="stat-valor" id="stat-ejemplares"><?= (int) $stats['ejemplares'] ?></p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Ejemplares Disponibles</p>
    <p class="stat-valor" id="stat-disponibles"><?= (int) $stats['disponibles'] ?></p>
    <span class="stat-trend" style="color:var(--exito)"><?= $stats['ejemplares'] > 0 ? round($stats['disponibles'] / $stats['ejemplares'] * 100) : 0 ?>% total</span>
  </div>
  <div class="stat-card<?= $stats['enReparacion'] > 0 ? ' stat-error' : '' ?>">
    <p class="stat-label">En Reparación</p>
    <p class="stat-valor" id="stat-reparacion"><?= (int) $stats['enReparacion'] ?></p>
    <?php if ($stats['enReparacion'] > 0): ?><span class="stat-trend" style="color:var(--error)">Revisión pendiente</span><?php endif; ?>
  </div>
</div>

<div class="tabla-contenedor">
  <div class="tabla-filtros">
    <input type="search" id="buscar-libro" placeholder="Buscar por título, autor o ISBN...">
    <select id="filtro-genero">
      <option value="">Género</option>
      <?php foreach ($generos as $genero): ?>
        <option value="<?= (int) $genero['genero_id'] ?>"><?= htmlspecialchars($genero['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filtro-editorial">
      <option value="">Editorial</option>
      <?php foreach ($editoriales as $editorial): ?>
        <option value="<?= (int) $editorial['editorial_id'] ?>"><?= htmlspecialchars($editorial['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filtro-estado">
      <option value="">Disponibilidad</option>
      <option value="DISPONIBLE">DISPONIBLE</option>
      <option value="PARCIAL">PARCIAL</option>
      <option value="AGOTADO">AGOTADO</option>
    </select>
  </div>
  <div class="tabla-scroll">
    <table class="tabla">
      <thead><tr><th>Portada</th><th>Libro</th><th>ISBN</th><th>Categoría</th><th>Disponibilidad</th><th>Acciones</th></tr></thead>
      <tbody id="tabla-libros">
        <?php if (empty($libros)): ?>
          <tr><td colspan="6">No se encontraron libros con ese criterio.</td></tr>
        <?php endif; ?>
        <?php foreach ($libros as $libro): ?>
          <?php $clase = $libro['disponibilidad'] === 'DISPONIBLE' ? 'badge-exito' : ($libro['disponibilidad'] === 'PARCIAL' ? 'badge-alerta' : 'badge-error'); ?>
          <tr>
            <td>
              <?php if (!empty($libro['portada_url'])): ?>
                <div class="portada-libro"><img src="img/<?= htmlspecialchars($libro['portada_url']) ?>" alt="Portada de <?= htmlspecialchars($libro['titulo']) ?>"></div>
              <?php else: ?>
                <div class="portada-libro"><?= htmlspecialchars(mb_strtoupper(mb_substr($libro['titulo'], 0, 2))) ?></div>
              <?php endif; ?>
            </td>
            <td><div class="libro-info"><span class="libro-titulo"><?= htmlspecialchars($libro['titulo']) ?></span><span class="libro-autor"><?= htmlspecialchars($libro['autor']) ?></span></div></td>
            <td><span class="isbn-texto"><?= htmlspecialchars($libro['isbn']) ?></span></td>
            <td><?= htmlspecialchars($libro['genero'] ?? 'Sin categoría') ?></td>
            <td><span class="badge <?= $clase ?>"><?= (int) $libro['disponibles'] ?> de <?= (int) $libro['ejemplares'] ?> disponibles</span></td>
            <td class="acciones-celda">
              <button class="btn-icono" title="Ver detalle" data-accion="ver" data-id="<?= (int) $libro['libro_id'] ?>"><i class="bi bi-eye"></i></button>
              <button class="btn-icono" title="Editar" data-accion="editar" data-id="<?= (int) $libro['libro_id'] ?>"><i class="bi bi-pencil"></i></button>
              <button class="btn-icono" title="Eliminar" data-accion="eliminar" data-id="<?= (int) $libro['libro_id'] ?>" data-titulo="<?= htmlspecialchars($libro['titulo']) ?>"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-libros"><?= count($libros) ?> libro<?= count($libros) === 1 ? '' : 's' ?> encontrados</span>
    <div class="paginacion" id="paginacion-libros"></div>
  </div>
</div>

<!-- Modal Registrar/Editar Libro -->
<div class="modal-overlay" id="modal-libro">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modal-libro-titulo">Registrar Nuevo Libro</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-libro"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div class="mensaje-error-servidor" id="mensaje-error-libro" style="display:none"></div>
      <form id="form-libro" novalidate>
        <input type="hidden" id="libro-id" value="">
        <div class="form-fila">
          <div class="form-grupo">
            <label for="libro-titulo">Título</label>
            <input type="text" id="libro-titulo" placeholder="Título del libro">
          </div>
          <div class="form-grupo">
            <label for="libro-autor">Autor</label>
            <input type="text" id="libro-autor" placeholder="Nombre del autor">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="libro-isbn">ISBN</label>
            <input type="text" id="libro-isbn" placeholder="978-0000000000">
          </div>
          <div class="form-grupo">
            <label for="libro-editorial">Editorial</label>
            <select id="libro-editorial">
              <option value="">Seleccionar editorial</option>
              <?php foreach ($editoriales as $editorial): ?>
                <option value="<?= (int) $editorial['editorial_id'] ?>"><?= htmlspecialchars($editorial['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="libro-genero">Género</label>
            <select id="libro-genero">
              <option value="">Seleccionar género</option>
              <?php foreach ($generos as $genero): ?>
                <option value="<?= (int) $genero['genero_id'] ?>"><?= htmlspecialchars($genero['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-grupo">
            <label for="libro-anio">Año de Publicación</label>
            <input type="number" id="libro-anio" placeholder="2026">
          </div>
        </div>
        <div class="form-grupo">
          <label for="libro-portada">URL de Portada</label>
          <input type="url" id="libro-portada" placeholder="https://ejemplo.com/portada.jpg">
        </div>
        <div class="form-grupo" id="grupo-ejemplares-iniciales">
          <label for="libro-ejemplares">Ejemplares Iniciales</label>
          <input type="number" id="libro-ejemplares" placeholder="1" min="1">
          <p class="form-nota">Crea esta cantidad de copias físicas. Para agregar o dar de baja copias de un libro ya registrado, usa Inventario.</p>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-libro">Cancelar</button>
      <button class="btn btn-primario" id="btn-guardar-libro">Guardar Libro</button>
    </div>
  </div>
</div>

<!-- Modal Detalle Libro -->
<div class="modal-overlay" id="modal-detalle-libro">
  <div class="modal">
    <div class="modal-header">
      <h2>Detalle del Libro</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-detalle-libro"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="detalle-libro-contenido"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-detalle-libro">Cerrar</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-eliminar-libro">
  <div class="modal modal-confirmacion">
    <div class="modal-header">
      <h2>Eliminar Libro</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-eliminar-libro"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <p id="texto-eliminar-libro">Confirma si deseas eliminar este libro del catálogo.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-eliminar-libro">Cancelar</button>
      <button class="btn btn-primario" id="btn-confirmar-eliminar-libro">Eliminar</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
