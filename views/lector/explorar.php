<?php
$pageTitle         = 'Explorar Libros';
$pageCss           = 'explorar.css';
$topbarTitulo      = 'Explorar Catálogo';
$searchPlaceholder = 'Buscar libros...';
$activeNav         = 'explorar';
$pageJs            = ['js/explorar.js'];

$busqueda = $_GET['q'] ?? '';
$generoSeleccionado = isset($_GET['genero']) ? (int) $_GET['genero'] : 0;

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Explorar Libros</h1>
    <p>Descubre el catálogo de la biblioteca y solicita un préstamo.</p>
  </div>
</div>

<div class="mensaje-exito mensaje-catalogo" id="mensaje-catalogo" style="display:none"></div>

<div class="tabla-contenedor">
  <form class="tabla-filtros" method="GET" action="index.php" id="form-filtros-catalogo">
    <input type="hidden" name="controller" value="lector">
    <input type="hidden" name="action" value="explorar">
    <input type="search" name="q" id="buscar-catalogo" placeholder="Buscar por título o autor..." value="<?= htmlspecialchars($busqueda) ?>">
    <select name="genero" id="filtro-cat-genero">
      <option value="">Todas las categorías</option>
      <?php foreach ($generos as $genero): ?>
        <option value="<?= (int) $genero['genero_id'] ?>" <?= $generoSeleccionado === (int) $genero['genero_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($genero['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Buscar</button>
  </form>
  <div class="tabla-scroll">
    <table class="tabla">
      <thead><tr><th>Portada</th><th>Libro</th><th>Categoría</th><th>Disponibilidad</th><th>Acción</th></tr></thead>
      <tbody id="tabla-catalogo">
        <?php if (empty($catalogo)): ?>
          <tr><td colspan="5">No se encontraron libros con ese criterio.</td></tr>
        <?php endif; ?>
        <?php foreach ($catalogo as $libro): $disponible = $libro['disponibles'] > 0; ?>
          <tr>
            <td>
              <?php if (!empty($libro['portada_url'])): ?>
                <div class="portada-libro"><img src="img/<?= htmlspecialchars($libro['portada_url']) ?>" alt="Portada de <?= htmlspecialchars($libro['titulo']) ?>"></div>
              <?php else: ?>
                <div class="portada-libro"><?= htmlspecialchars(mb_strtoupper(mb_substr($libro['titulo'], 0, 2))) ?></div>
              <?php endif; ?>
            </td>
            <td><div class="libro-info"><span class="libro-titulo"><?= htmlspecialchars($libro['titulo']) ?></span><span class="libro-autor"><?= htmlspecialchars($libro['autor']) ?></span></div></td>
            <td><?= htmlspecialchars($libro['genero'] ?? 'Sin categoría') ?></td>
            <td><span class="badge <?= $disponible ? 'badge-exito' : 'badge-error' ?>"><?= $disponible ? (int) $libro['disponibles'] . ' disponibles' : 'No disponible' ?></span></td>
            <td>
              <?php if ($disponible): ?>
                <button type="button" class="btn btn-sm btn-primario" data-id="<?= (int) $libro['libro_id'] ?>" data-titulo="<?= htmlspecialchars($libro['titulo']) ?>">Solicitar</button>
              <?php else: ?>
                <button type="button" class="btn btn-sm btn-outline" disabled>No disponible</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="modal-catalogo">
  <div class="modal modal-confirmacion">
    <div class="modal-header">
      <h2 id="modal-catalogo-titulo">Confirmar Solicitud</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-catalogo"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="index.php?controller=lector&action=solicitar" id="form-catalogo">
      <input type="hidden" name="libro_id" id="catalogo-libro-id" value="">
      <div class="modal-body">
        <p id="texto-catalogo">Confirma la acción para este libro.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-catalogo">Cancelar</button>
        <button type="submit" class="btn btn-primario" id="btn-confirmar-catalogo">Confirmar</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
