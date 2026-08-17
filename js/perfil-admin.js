//Perfil administrativo: modales de edición, guardados por AJAX sin recargar la página.

$(function () {

  let formPerfil = $('#form-perfil-admin');
  let formClave = $('#form-clave-admin');
  let errorPerfil = $('#mensaje-error-perfil-admin');
  let errorClave = $('#mensaje-error-clave-admin');

  $('#btn-editar-perfil-admin').on('click', function () {
    errorPerfil.hide().text('');
    $('#modal-editar-perfil-admin').addClass('visible');
  });

  $('#btn-cambiar-clave-admin').on('click', function () {
    formClave[0].reset();
    errorClave.hide().text('');
    $('#modal-cambiar-clave-admin').addClass('visible');
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
      $('#perfil-admin-correo-valor').text(data.usuario.correo);
      $('#perfil-admin-telefono-valor').text(data.usuario.telefono);
      $('#perfil-admin-direccion-valor').text(data.usuario.direccion);
      $('#modal-editar-perfil-admin').removeClass('visible');
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
      $('#modal-cambiar-clave-admin').removeClass('visible');
    }, 'json');
  });

});
