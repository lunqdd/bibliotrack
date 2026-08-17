//Panel del lector: modal de solicitud de préstamo, enviado por AJAX.
//Las estadísticas, actividad y recomendados se renderizan en PHP.

$(function () {

  let modal = $('#modal-prestamo');
  let formulario = $('#form-prestamo');
  let mensajeExito = $('#mensaje-exito-prestamo');
  let mensajeError = $('#mensaje-error-prestamo');

  $('#btn-nuevo-prestamo').on('click', function () {
    formulario[0].reset();
    mensajeExito.hide().text('');
    mensajeError.hide().text('');
    modal.addClass('visible');
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Cierra el modal si se hace clic fuera del contenido.
  modal.on('click', function (evento) {
    if (evento.target === modal[0]) {
      modal.removeClass('visible');
    }
  });

  //Envía la solicitud de préstamo sin recargar la página.
  formulario.on('submit', function (evento) {
    evento.preventDefault();
    mensajeExito.hide().text('');
    mensajeError.hide().text('');

    $.post(formulario.attr('action'), formulario.serialize(), function (data) {
      if (data.response === '00') {
        mensajeExito.text(data.message).show();
        formulario[0].reset();
        setTimeout(function () { modal.removeClass('visible'); }, 1500);
      } else {
        mensajeError.text(data.message).show();
      }
    }, 'json');
  });

});
