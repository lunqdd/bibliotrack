<?php
$pageTitle         = 'Gestión de Libros';
$pageCss           = 'libros.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar libro...';
$activeNav         = 'libros';
$pageJs            = ['js/libros.js'];

// Disponibilidad calculada a partir de ejemplares/disponibles de cada libro
function disponibilidadLibro(array $libro): string
{
    if ($libro['disponibles'] <= 0) return 'AGOTADO';
    if ($libro['disponibles'] < $libro['ejemplares']) return 'PARCIAL';
    return 'DISPONIBLE';
}

function claseBadgeDisponibilidad(string $estado): string
{
    if ($estado === 'DISPONIBLE') return 'badge-exito';
    if ($estado === 'PARCIAL') return 'badge-alerta';
    return 'badge-error';
}

$generosNombres = array_column($generos, 'nombre');
$editorialesNombres = array_column($editoriales, 'nombre');
$autoresNombres = array_column($autores, 'nombre');

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Gestión de Libros</h1>
    <p>Administra el catálogo completo, existencias y disponibilidad de la biblioteca.</p>
  </div>
  <div class="pagina-acciones">
    <button class="btn btn-primario" id="btn-nuevo-libro"><i class="bi bi-plus-lg"></i> Registrar Nuevo Libro</button>
  </div>
</div>

<div class="fila-stats">
  <div class="stat-card">
    <p class="stat-label">Títulos Registrados</p>
    <p class="stat-valor"><?= (int) $stats['titulos'] ?></p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Total Ejemplares</p>
    <p class="stat-valor"><?= (int) $stats['totalEjemplares'] ?></p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Ejemplares Disponibles</p>
    <p class="stat-valor"><?= (int) $stats['disponibles'] ?></p>
    <span class="stat-trend" style="color:var(--exito)"><?= $stats['totalEjemplares'] > 0 ? round(($stats['disponibles'] / $stats['totalEjemplares']) * 100) : 0 ?>% total</span>
  </div>
</div>

<div class="tabla-contenedor">
  <div class="tabla-filtros">
    <input type="search" id="buscar-libro" placeholder="Buscar por título, autor o ISBN...">
    <select id="filtro-genero">
      <option value="">Género</option>
      <?php foreach ($generosNombres as $nombre): ?>
        <option value="<?= htmlspecialchars($nombre) ?>"><?= htmlspecialchars($nombre) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filtro-editorial">
      <option value="">Editorial</option>
      <?php foreach ($editorialesNombres as $nombre): ?>
        <option value="<?= htmlspecialchars($nombre) ?>"><?= htmlspecialchars($nombre) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filtro-estado">
      <option value="">Disponibilidad</option>
      <option value="DISPONIBLE">DISPONIBLE</option>
      <option value="PARCIAL">PARCIAL</option>
      <option value="AGOTADO">AGOTADO</option>
    </select>
  </div>
  <div class="tabla-scroll"><table class="tabla">
    <thead><tr><th>Portada</th><th>Libro</th><th>ISBN</th><th>Categoría</th><th>Disponibilidad</th><th>Acciones</th></tr></thead>
    <tbody id="tabla-libros">
      <?php foreach ($libros as $libro): $estadoCalc = disponibilidadLibro($libro); ?>
        <tr data-texto="<?= htmlspecialchars(mb_strtolower($libro['titulo'] . ' ' . $libro['autor'] . ' ' . $libro['isbn'])) ?>"
            data-genero="<?= htmlspecialchars($libro['genero'] ?? '') ?>"
            data-editorial="<?= htmlspecialchars($libro['editorial'] ?? '') ?>"
            data-disponibilidad="<?= $estadoCalc ?>">
          <td>
            <?php if ($libro['portada_url']): ?>
              <div class="portada-libro"><img src="img/<?= htmlspecialchars($libro['portada_url']) ?>" alt="Portada de <?= htmlspecialchars($libro['titulo']) ?>"></div>
            <?php else: ?>
              <div class="portada-libro"><?= htmlspecialchars(mb_strtoupper(mb_substr($libro['titulo'], 0, 2))) ?></div>
            <?php endif; ?>
          </td>
          <td><div class="libro-info"><span class="libro-titulo"><?= htmlspecialchars($libro['titulo']) ?></span><span class="libro-autor"><?= htmlspecialchars($libro['autor']) ?></span></div></td>
          <td><span class="isbn-texto"><?= htmlspecialchars($libro['isbn']) ?></span></td>
          <td><?= htmlspecialchars($libro['genero'] ?? '') ?></td>
          <td><span class="badge <?= claseBadgeDisponibilidad($estadoCalc) ?>"><?= (int) $libro['disponibles'] ?> de <?= (int) $libro['ejemplares'] ?> disponibles</span></td>
          <td class="acciones-celda">
            <button class="btn-icono" title="Ver detalle" data-accion="ver"
              data-titulo="<?= htmlspecialchars($libro['titulo'], ENT_QUOTES) ?>"
              data-autor="<?= htmlspecialchars($libro['autor'], ENT_QUOTES) ?>"
              data-isbn="<?= htmlspecialchars($libro['isbn'], ENT_QUOTES) ?>"
              data-editorial="<?= htmlspecialchars($libro['editorial'] ?? '', ENT_QUOTES) ?>"
              data-genero="<?= htmlspecialchars($libro['genero'] ?? '', ENT_QUOTES) ?>"
              data-anio="<?= (int) $libro['anio_publicacion'] ?>"
              data-portada="<?= htmlspecialchars($libro['portada_url'] ?? '', ENT_QUOTES) ?>"
              data-disponibles="<?= (int) $libro['disponibles'] ?>"
              data-ejemplares="<?= (int) $libro['ejemplares'] ?>"
              data-estado="<?= $estadoCalc ?>"
            ><i class="bi bi-eye"></i></button>
            <button class="btn-icono" title="Editar" data-accion="editar"
              data-id="<?= (int) $libro['libro_id'] ?>"
              data-titulo="<?= htmlspecialchars($libro['titulo'], ENT_QUOTES) ?>"
              data-autor="<?= htmlspecialchars($libro['autor'], ENT_QUOTES) ?>"
              data-isbn="<?= htmlspecialchars($libro['isbn'], ENT_QUOTES) ?>"
              data-editorial="<?= htmlspecialchars($libro['editorial'] ?? '', ENT_QUOTES) ?>"
              data-genero="<?= htmlspecialchars($libro['genero'] ?? '', ENT_QUOTES) ?>"
              data-anio="<?= (int) $libro['anio_publicacion'] ?>"
              data-portada="<?= htmlspecialchars($libro['portada_url'] ?? '', ENT_QUOTES) ?>"
            ><i class="bi bi-pencil"></i></button>
            <button class="btn-icono" title="Eliminar" data-accion="eliminar" data-id="<?= (int) $libro['libro_id'] ?>" data-titulo="<?= htmlspecialchars($libro['titulo'], ENT_QUOTES) ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-libros">Mostrando <?= count($libros) ?> de <?= count($libros) ?> libros</span>
  </div>
</div>

<!-- Modal Registrar/Editar Libro -->
<div class="modal-overlay" id="modal-libro">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modal-libro-titulo">Registrar Nuevo Libro</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-libro"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="form-libro" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-libro" style="display:none"></div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="libro-titulo">Título</label>
            <input type="text" id="libro-titulo" name="titulo" placeholder="Título del libro">
          </div>
          <div class="form-grupo">
            <label for="libro-autor">Autor</label>
            <input type="text" id="libro-autor" name="autor" placeholder="Nombre del autor" list="lista-autores">
            <datalist id="lista-autores">
              <?php foreach ($autoresNombres as $nombre): ?><option value="<?= htmlspecialchars($nombre) ?>"><?php endforeach; ?>
            </datalist>
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="libro-isbn">ISBN</label>
            <input type="text" id="libro-isbn" name="isbn" placeholder="978-0000000000">
          </div>
          <div class="form-grupo">
            <label for="libro-editorial">Editorial</label>
            <input type="text" id="libro-editorial" name="editorial" placeholder="Nombre de la editorial" list="lista-editoriales">
            <datalist id="lista-editoriales">
              <?php foreach ($editorialesNombres as $nombre): ?><option value="<?= htmlspecialchars($nombre) ?>"><?php endforeach; ?>
            </datalist>
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="libro-genero">Género</label>
            <input type="text" id="libro-genero" name="genero" placeholder="Género" list="lista-generos">
            <datalist id="lista-generos">
              <?php foreach ($generosNombres as $nombre): ?><option value="<?= htmlspecialchars($nombre) ?>"><?php endforeach; ?>
            </datalist>
          </div>
          <div class="form-grupo">
            <label for="libro-anio">Año de Publicación</label>
            <input type="number" id="libro-anio" name="anio" placeholder="2026">
          </div>
        </div>
        <div class="form-grupo">
          <label for="libro-portada">Nombre de archivo de Portada</label>
          <input type="text" id="libro-portada" name="portada_url" placeholder="portada.jpg (dentro de img/)">
        </div>
        <div class="form-grupo" id="grupo-ejemplares-iniciales">
          <label for="libro-ejemplares">Ejemplares Iniciales</label>
          <input type="number" id="libro-ejemplares" name="ejemplares" placeholder="1" min="1">
          <p class="form-nota">Crea esta cantidad de copias físicas. Para agregar o dar de baja copias de un libro ya registrado, usa Inventario.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-libro">Cancelar</button>
        <button type="submit" class="btn btn-primario">Guardar Libro</button>
      </div>
    </form>
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
