// Catálogo del lector: búsqueda por AJAX y confirmación de solicitud de préstamo.

$(function () {

  let modal = $('#modal-catalogo');
  let tabla = $('#tabla-catalogo');
  let mensajeCatalogo = $('#mensaje-catalogo');
  let campoBusqueda = $('#buscar-catalogo');
  let filtroGenero = $('#filtro-cat-genero');
  let temporizadorBusqueda = null;

  //Arma una fila de la tabla con el mismo formato que hoy genera PHP.
  function filaLibro(libro) {
    let disponible = libro.disponibles > 0;
    let portada = libro.portada_url
      ? '<div class="portada-libro"><img src="img/' + libro.portada_url + '" alt="Portada de ' + libro.titulo + '"></div>'
      : '<div class="portada-libro">' + libro.titulo.substring(0, 2).toUpperCase() + '</div>';
    let disponibilidad = disponible
      ? '<span class="badge badge-exito">' + libro.disponibles + ' disponibles</span>'
      : '<span class="badge badge-error">No disponible</span>';
    let accion = disponible
      ? '<button type="button" class="btn btn-sm btn-primario" data-id="' + libro.libro_id + '" data-titulo="' + libro.titulo + '">Solicitar</button>'
      : '<button type="button" class="btn btn-sm btn-outline" disabled>No disponible</button>';

    return '<tr>'
      + '<td>' + portada + '</td>'
      + '<td><div class="libro-info"><span class="libro-titulo">' + libro.titulo + '</span><span class="libro-autor">' + libro.autor + '</span></div></td>'
      + '<td>' + (libro.genero || 'Sin categoría') + '</td>'
      + '<td>' + disponibilidad + '</td>'
      + '<td>' + accion + '</td>'
      + '</tr>';
  }

  //Pide el catálogo filtrado y repinta la tabla sin recargar la página.
  function buscarCatalogo() {
    $.get('index.php?controller=lector&action=buscarCatalogo', {
      q: campoBusqueda.val(),
      genero: filtroGenero.val(),
    }, function (data) {
      let lista = data.catalogo || [];

      if (!lista.length) {
        tabla.html('<tr><td colspan="5">No se encontraron libros con ese criterio.</td></tr>');
        return;
      }

      tabla.empty();
      lista.forEach(function (libro) { tabla.append(filaLibro(libro)); });
    }, 'json');
  }

  campoBusqueda.on('input', function () {
    clearTimeout(temporizadorBusqueda);
    temporizadorBusqueda = setTimeout(buscarCatalogo, 300);
  });

  filtroGenero.on('change', buscarCatalogo);

  $('#form-filtros-catalogo').on('submit', function (evento) {
    evento.preventDefault();
    buscarCatalogo();
  });

  tabla.on('click', '[data-id]', function () {
    let titulo = $(this).data('titulo');
    $('#catalogo-libro-id').val($(this).data('id'));
    $('#texto-catalogo').text('Confirma si deseas solicitar "' + titulo + '".');
    modal.addClass('visible');
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  modal.on('click', function (evento) {
    if (evento.target === modal[0]) {
      modal.removeClass('visible');
    }
  });

  //Confirma la solicitud de préstamo por AJAX.
  $('#form-catalogo').on('submit', function (evento) {
    evento.preventDefault();
    let formulario = $(this);

    $.post(formulario.attr('action'), formulario.serialize(), function (data) {
      modal.removeClass('visible');
      mensajeCatalogo
        .toggleClass('mensaje-error-servidor', data.response !== '00')
        .text(data.message)
        .show();

      if (data.response === '00') {
        buscarCatalogo();
      }
    }, 'json');
  });

});
