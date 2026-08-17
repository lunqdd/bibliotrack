//Préstamos y devoluciones: filtro sobre la tabla renderizada por el servidor y formularios por AJAX

$(function () {

  let formPrestamo = $('#form-prestamo');
  let formDevolucion = $('#form-devolucion');
  let errorPrestamo = $('#mensaje-error-prestamo');
  let errorDevolucion = $('#mensaje-error-devolucion');

  //Filtra las filas ya renderizadas según el estado seleccionado
  function filtrar() {
    let estado = $('#filtro-estado-prestamo').val();
    $('#tabla-prestamos tr').each(function () {
      let fila = $(this);
      fila.toggle(!estado || fila.data('estado') === estado);
    });
  }

  //Muestra los datos completos del préstamo en el modal de detalle
  function verDetalle(boton) {
    let contenido =
      '<div class="detalle-grid">' +
        '<div class="detalle-campo"><div class="detalle-label">Usuario</div><div class="detalle-valor">' + boton.data('nombre') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">ID</div><div class="detalle-valor">' + boton.data('codigo') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Libro</div><div class="detalle-valor">' + boton.data('titulo') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">ISBN</div><div class="detalle-valor">' + boton.data('isbn') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Fecha Préstamo</div><div class="detalle-valor">' + boton.data('fecha-prestamo') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Fecha Devolución Programada</div><div class="detalle-valor">' + boton.data('fecha-programada') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Fecha Devolución Real</div><div class="detalle-valor">' + (boton.data('fecha-real') || '—') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Estado</div><div class="detalle-valor">' + boton.data('estado') + '</div></div>' +
        (boton.data('estado-ejemplar') ? '<div class="detalle-campo"><div class="detalle-label">Estado del Ejemplar al Devolver</div><div class="detalle-valor">' + boton.data('estado-ejemplar') + '</div></div>' : '') +
        (boton.data('notas') ? '<div class="detalle-campo"><div class="detalle-label">Notas de Devolución</div><div class="detalle-valor">' + boton.data('notas') + '</div></div>' : '') +
      '</div>';
    $('#detalle-prestamo-contenido').html(contenido);
    $('#modal-detalle-prestamo').addClass('visible');
  }

  $('#tabla-prestamos').on('click', '[data-accion="ver"]', function () {
    verDetalle($(this));
  });

  //Aprueba una solicitud del lector: crea el préstamo real y quita la fila
  $('#tabla-solicitudes').on('click', '[data-accion="aprobar"]', function () {
    let solicitudId = $(this).data('id');
    $.post('index.php?controller=prestamos&action=aprobarSolicitud', { solicitud_id: solicitudId }, function (data) {
      if (data.response !== '00') {
        alert(data.message);
        return;
      }
      window.location.reload();
    }, 'json');
  });

  //Rechaza una solicitud del lector sin crear préstamo
  $('#tabla-solicitudes').on('click', '[data-accion="rechazar"]', function () {
    let solicitudId = $(this).data('id');
    $.post('index.php?controller=prestamos&action=rechazarSolicitud', { solicitud_id: solicitudId }, function (data) {
      if (data.response !== '00') {
        alert(data.message);
        return;
      }
      window.location.reload();
    }, 'json');
  });

  $('#btn-abrir-prestamo').on('click', function () {
    errorPrestamo.hide().text('');
    formPrestamo[0].reset();
    $('#modal-prestamo').addClass('visible');
  });

  $('#btn-abrir-devolucion').on('click', function () {
    errorDevolucion.hide().text('');
    formDevolucion[0].reset();
    $('#modal-devolucion').addClass('visible');
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Registra un préstamo nuevo tomando el primer ejemplar disponible del libro elegido
  formPrestamo.on('submit', function (evento) {
    evento.preventDefault();
    errorPrestamo.hide().text('');

    $.post('index.php?controller=prestamos&action=store', formPrestamo.serialize(), function (data) {
      if (data.response !== '00') {
        errorPrestamo.text(data.message).show();
        return;
      }
      window.location.reload();
    }, 'json');
  });

  //Cierra un préstamo activo con su devolución
  formDevolucion.on('submit', function (evento) {
    evento.preventDefault();
    errorDevolucion.hide().text('');

    $.post('index.php?controller=prestamos&action=registrarDevolucion', formDevolucion.serialize(), function (data) {
      if (data.response !== '00') {
        errorDevolucion.text(data.message).show();
        return;
      }
      window.location.reload();
    }, 'json');
  });

  $('#filtro-estado-prestamo').on('change', filtrar);

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#nuevo-prestamo') {
    $('#btn-abrir-prestamo').trigger('click');
  }

  //Abre el modal de devolución si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-devolucion') {
    $('#btn-abrir-devolucion').trigger('click');
  }

});
