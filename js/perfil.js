//Perfil del lector: modales de edición, guardados por AJAX sin recargar la página.

$(function () {

  let formPerfil = $('#form-perfil');
  let formClave = $('#form-clave');
  let errorPerfil = $('#mensaje-error-perfil');
  let errorClave = $('#mensaje-error-clave');

  $('#btn-editar-perfil').on('click', function () {
    errorPerfil.hide().text('');
    $('#modal-editar-perfil').addClass('visible');
  });

  $('#btn-cambiar-clave').on('click', function () {
    formClave[0].reset();
    errorClave.hide().text('');
    $('#modal-cambiar-clave').addClass('visible');
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Guarda los datos personales y los refleja en la página sin recargar.
  formPerfil.on('submit', function (evento) {
    evento.preventDefault();
    errorPerfil.hide().text('');

    $.post(formPerfil.attr('action'), formPerfil.serialize(), function (data) {
      if (data.response !== '00') {
        errorPerfil.text(data.message).show();
        return;
      }

      $('.perfil-nombre').text(data.usuario.nombre_completo);
      $('#perfil-correo-valor').text(data.usuario.correo);
      $('#perfil-telefono-valor').text(data.usuario.telefono);
      $('#perfil-direccion-valor').text(data.usuario.direccion);
      $('#modal-editar-perfil').removeClass('visible');
    }, 'json');
  });

  //Cambia la contraseña por AJAX.
  formClave.on('submit', function (evento) {
    evento.preventDefault();
    errorClave.hide().text('');

    $.post(formClave.attr('action'), formClave.serialize(), function (data) {
      if (data.response !== '00') {
        errorClave.text(data.message).show();
        return;
      }

      formClave[0].reset();
      $('#modal-cambiar-clave').removeClass('visible');
    }, 'json');
  });

});
