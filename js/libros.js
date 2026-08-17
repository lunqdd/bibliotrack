//Gestión de libros: filtros sobre la tabla renderizada por el servidor y modales por AJAX

$(function () {

  let form = $('#form-libro');
  let error = $('#mensaje-error-libro');
  let idEdicion = null;
  let idEliminar = null;

  //Filtra las filas ya renderizadas por texto, género, editorial y disponibilidad
  function filtrar() {
    let busqueda = $('#buscar-libro').val().toLowerCase();
    let genero = $('#filtro-genero').val();
    let editorial = $('#filtro-editorial').val();
    let estado = $('#filtro-estado').val();

    $('#tabla-libros tr').each(function () {
      let fila = $(this);
      let coincide = fila.data('texto').indexOf(busqueda) !== -1 &&
        (!genero || fila.data('genero') === genero) &&
        (!editorial || fila.data('editorial') === editorial) &&
        (!estado || fila.data('disponibilidad') === estado);
      fila.toggle(coincide);
    });
  }

  //Abre el formulario limpio para registrar un libro nuevo
  function abrirModalNuevo() {
    idEdicion = null;
    error.hide().text('');
    form[0].reset();
    $('#modal-libro-titulo').text('Registrar Nuevo Libro');
    $('#grupo-ejemplares-iniciales').show();
    $('#modal-libro').addClass('visible');
  }

  //Carga los datos del libro en el modal de edición. Los ejemplares no se tocan aquí
  function abrirModalEditar(boton) {
    idEdicion = boton.data('id');
    error.hide().text('');
    $('#modal-libro-titulo').text('Editar Libro');
    $('#libro-titulo').val(boton.data('titulo'));
    $('#libro-autor').val(boton.data('autor'));
    $('#libro-isbn').val(boton.data('isbn'));
    $('#libro-editorial').val(boton.data('editorial'));
    $('#libro-genero').val(boton.data('genero'));
    $('#libro-anio').val(boton.data('anio'));
    $('#libro-portada').val(boton.data('portada'));
    $('#grupo-ejemplares-iniciales').hide();
    $('#modal-libro').addClass('visible');
  }

  //Muestra los datos del libro en modo lectura
  function verDetalle(boton) {
    let contenido =
      '<div class="detalle-grid">' +
      '<div class="detalle-campo"><div class="detalle-label">Título</div><div class="detalle-valor">' + boton.data('titulo') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Autor</div><div class="detalle-valor">' + boton.data('autor') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">ISBN</div><div class="detalle-valor">' + boton.data('isbn') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Editorial</div><div class="detalle-valor">' + boton.data('editorial') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Género</div><div class="detalle-valor">' + boton.data('genero') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Año</div><div class="detalle-valor">' + boton.data('anio') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Ejemplares</div><div class="detalle-valor">' + boton.data('disponibles') + ' de ' + boton.data('ejemplares') + ' disponibles</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Disponibilidad</div><div class="detalle-valor">' + boton.data('estado') + '</div></div>' +
      '</div>';
    $('#detalle-libro-contenido').html(contenido);
    $('#modal-detalle-libro').addClass('visible');
  }

  //Pide confirmación antes de eliminar un libro del catálogo
  function pedirConfirmacionEliminar(boton) {
    idEliminar = boton.data('id');
    $('#texto-eliminar-libro').text('Confirma si deseas eliminar "' + boton.data('titulo') + '" del catálogo.');
    $('#modal-eliminar-libro').addClass('visible');
  }

  $('#btn-nuevo-libro').on('click', abrirModalNuevo);

  $('#tabla-libros').on('click', '[data-accion]', function () {
    let boton = $(this);
    let accion = boton.data('accion');
    if (accion === 'ver') verDetalle(boton);
    if (accion === 'editar') abrirModalEditar(boton);
    if (accion === 'eliminar') pedirConfirmacionEliminar(boton);
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Registra o actualiza el libro según si hay un id en edición
  form.on('submit', function (evento) {
    evento.preventDefault();
    error.hide().text('');

    let url = idEdicion
      ? 'index.php?controller=libros&action=update&id=' + idEdicion
      : 'index.php?controller=libros&action=store';

    $.post(url, form.serialize(), function (data) {
      if (data.response !== '00') {
        error.text(data.message).show();
        return;
      }
      window.location.reload();
    }, 'json');
  });

  $('#btn-confirmar-eliminar-libro').on('click', function () {
    if (!idEliminar) return;
    $.post('index.php?controller=libros&action=delete&id=' + idEliminar, function (data) {
      if (data.response !== '00') {
        $('#modal-eliminar-libro').removeClass('visible');
        alert(data.message);
        return;
      }
      window.location.reload();
    }, 'json');
  });

  $('#buscar-libro').on('input', filtrar);
  $('#filtro-genero').on('change', filtrar);
  $('#filtro-editorial').on('change', filtrar);
  $('#filtro-estado').on('change', filtrar);

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-libro') {
    abrirModalNuevo();
  }

});
