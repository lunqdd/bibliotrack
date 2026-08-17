// Gestión de libros: filtros, altas, ediciones y bajas contra el servidor por AJAX.

$(function () {

  let tabla = $('#tabla-libros');
  let infoPaginacion = $('#info-paginacion-libros');
  let modalLibro = $('#modal-libro');
  let modalDetalle = $('#modal-detalle-libro');
  let modalEliminar = $('#modal-eliminar-libro');
  let mensajeLibro = $('#mensaje-error-libro');
  let idEliminar = 0;
  let temporizadorBusqueda = null;

  function claseBadge(estado) {
    if (estado === 'DISPONIBLE') return 'badge-exito';
    if (estado === 'PARCIAL') return 'badge-alerta';
    return 'badge-error';
  }

  function portadaLibro(libro) {
    if (libro.portada_url) {
      return '<div class="portada-libro"><img src="img/' + libro.portada_url + '" alt="Portada de ' + libro.titulo + '"></div>';
    }
    return '<div class="portada-libro">' + libro.titulo.substring(0, 2).toUpperCase() + '</div>';
  }

  function filaLibro(libro) {
    return '<tr>'
      + '<td>' + portadaLibro(libro) + '</td>'
      + '<td><div class="libro-info"><span class="libro-titulo">' + libro.titulo + '</span><span class="libro-autor">' + libro.autor + '</span></div></td>'
      + '<td><span class="isbn-texto">' + libro.isbn + '</span></td>'
      + '<td>' + (libro.genero || 'Sin categoría') + '</td>'
      + '<td><span class="badge ' + claseBadge(libro.disponibilidad) + '">' + libro.disponibles + ' de ' + libro.ejemplares + ' disponibles</span></td>'
      + '<td class="acciones-celda">'
      + '<button class="btn-icono" title="Ver detalle" data-accion="ver" data-id="' + libro.libro_id + '"><i class="bi bi-eye"></i></button>'
      + '<button class="btn-icono" title="Editar" data-accion="editar" data-id="' + libro.libro_id + '"><i class="bi bi-pencil"></i></button>'
      + '<button class="btn-icono" title="Eliminar" data-accion="eliminar" data-id="' + libro.libro_id + '" data-titulo="' + libro.titulo + '"><i class="bi bi-trash"></i></button>'
      + '</td></tr>';
  }

  //Pide el catálogo filtrado y repinta la tabla sin recargar la página.
  function buscarLibros() {
    $.get('index.php?controller=libro&action=buscarLibros', {
      q: $('#buscar-libro').val(),
      genero: $('#filtro-genero').val(),
      editorial: $('#filtro-editorial').val(),
      estado: $('#filtro-estado').val(),
    }, function (data) {
      let lista = data.libros || [];

      if (!lista.length) {
        tabla.html('<tr><td colspan="6">No se encontraron libros con ese criterio.</td></tr>');
        infoPaginacion.text('0 libros encontrados');
        return;
      }

      tabla.empty();
      lista.forEach(function (libro) { tabla.append(filaLibro(libro)); });
      infoPaginacion.text(lista.length + (lista.length === 1 ? ' libro encontrado' : ' libros encontrados'));
    }, 'json');
  }

  $('#buscar-libro').on('input', function () {
    clearTimeout(temporizadorBusqueda);
    temporizadorBusqueda = setTimeout(buscarLibros, 300);
  });
  $('#filtro-genero, #filtro-editorial, #filtro-estado').on('change', buscarLibros);

  function limpiarFormulario() {
    $('#form-libro')[0].reset();
    $('#libro-id').val('');
    mensajeLibro.hide().text('');
  }

  function abrirModalNuevo() {
    limpiarFormulario();
    $('#modal-libro-titulo').text('Registrar Nuevo Libro');
    $('#grupo-ejemplares-iniciales').removeClass('oculto');
    modalLibro.addClass('visible');
  }

  //Trae los datos del libro y precarga el formulario de edición.
  function abrirModalEdicion(id) {
    $.get('index.php?controller=libro&action=edit', { id: id }, function (data) {
      if (data.response !== '00') return;
      let libro = data.libro;
      limpiarFormulario();
      $('#modal-libro-titulo').text('Editar Libro');
      $('#libro-id').val(libro.libro_id);
      $('#libro-titulo').val(libro.titulo);
      $('#libro-autor').val(libro.autor);
      $('#libro-isbn').val(libro.isbn);
      $('#libro-editorial').val(libro.editorial_id || '');
      $('#libro-genero').val(libro.genero_id || '');
      $('#libro-anio').val(libro.anio_publicacion || '');
      $('#libro-portada').val(libro.portada_url || '');
      $('#grupo-ejemplares-iniciales').addClass('oculto');
      modalLibro.addClass('visible');
    }, 'json');
  }

  //Muestra los datos del libro en modo lectura.
  function verDetalleLibro(id) {
    $.get('index.php?controller=libro&action=edit', { id: id }, function (data) {
      if (data.response !== '00') return;
      let libro = data.libro;
      let contenido =
        '<div class="detalle-grid">'
        + '<div class="detalle-campo"><div class="detalle-label">Portada</div><div class="detalle-valor">' + portadaLibro(libro) + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Título</div><div class="detalle-valor">' + libro.titulo + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Autor</div><div class="detalle-valor">' + libro.autor + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">ISBN</div><div class="detalle-valor">' + libro.isbn + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Editorial</div><div class="detalle-valor">' + (libro.editorial || 'Sin editorial') + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Género</div><div class="detalle-valor">' + (libro.genero || 'Sin género') + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Año</div><div class="detalle-valor">' + (libro.anio_publicacion || 'Sin dato') + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">URL de Portada</div><div class="detalle-valor">' + (libro.portada_url || 'Sin portada') + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Ejemplares</div><div class="detalle-valor">' + libro.disponibles + ' de ' + libro.ejemplares + ' disponibles</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Disponibilidad</div><div class="detalle-valor"><span class="badge ' + claseBadge(libro.disponibilidad) + '">' + libro.disponibilidad + '</span></div></div>'
        + '</div>';
      $('#detalle-libro-contenido').html(contenido);
      modalDetalle.addClass('visible');
    }, 'json');
  }

  //Pide confirmación antes de eliminar un libro del catálogo.
  function pedirConfirmacionEliminar(id, titulo) {
    idEliminar = id;
    $('#texto-eliminar-libro').text('Confirma si deseas eliminar "' + titulo + '" del catálogo.');
    modalEliminar.addClass('visible');
  }

  //Elimina el libro confirmado por AJAX y refresca el listado.
  $('#btn-confirmar-eliminar-libro').on('click', function () {
    if (!idEliminar) return;
    $.post('index.php?controller=libro&action=delete&id=' + idEliminar, {}, function (data) {
      modalEliminar.removeClass('visible');
      if (data.response === '00') {
        buscarLibros();
      } else {
        alert(data.message);
      }
      idEliminar = 0;
    }, 'json');
  });

  //Guarda un libro nuevo o actualiza los datos bibliográficos de uno existente.
  //Editar un libro nunca cambia su cantidad de ejemplares: eso se gestiona desde Inventario.
  $('#btn-guardar-libro').on('click', function () {
    mensajeLibro.hide().text('');

    let id = $('#libro-id').val();
    let datos = {
      titulo: $('#libro-titulo').val().trim(),
      autor: $('#libro-autor').val().trim(),
      isbn: $('#libro-isbn').val().trim(),
      editorial_id: $('#libro-editorial').val(),
      genero_id: $('#libro-genero').val(),
      anio: $('#libro-anio').val(),
      portada_url: $('#libro-portada').val().trim(),
      ejemplares: $('#libro-ejemplares').val(),
    };

    if (!datos.titulo || !datos.autor) {
      mensajeLibro.text('El título y el autor son obligatorios.').show();
      return;
    }

    let accion = id ? 'update' : 'store';
    let url = 'index.php?controller=libro&action=' + accion + (id ? '&id=' + id : '');

    $.post(url, datos, function (data) {
      if (data.response !== '00') {
        mensajeLibro.text(data.message).show();
        return;
      }
      modalLibro.removeClass('visible');
      buscarLibros();
    }, 'json');
  });

  //Despacha los botones de ver, editar y eliminar de cada fila
  tabla.on('click', '[data-accion]', function () {
    let boton = $(this);
    let id = boton.data('id');
    let accion = boton.data('accion');
    if (accion === 'ver') verDetalleLibro(id);
    if (accion === 'editar') abrirModalEdicion(id);
    if (accion === 'eliminar') pedirConfirmacionEliminar(id, boton.data('titulo'));
  });

  $('#btn-nuevo-libro').on('click', abrirModalNuevo);

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-libro') {
    abrirModalNuevo();
  }

});
