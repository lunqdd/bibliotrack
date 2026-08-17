//Gestión de usuarios: filtros sobre la tabla renderizada por el servidor y modal por AJAX

$(function () {

  let form = $('#form-usuario');
  let error = $('#mensaje-error-usuario');
  let idEdicion = null;

  //Filtra las filas ya renderizadas por texto, rol y estado
  function filtrar() {
    let busqueda = $('#buscar-usuario').val().toLowerCase();
    let rol = $('#filtro-rol').val();
    let estado = $('#filtro-estado-usuario').val();

    $('#tabla-usuarios tr').each(function () {
      let fila = $(this);
      let coincide = fila.data('nombre').indexOf(busqueda) !== -1 &&
        (!rol || fila.data('rol') === rol) &&
        (!estado || fila.data('estado') === estado);
      fila.toggle(coincide);
    });
  }

  //Abre el modal en blanco para registrar un usuario nuevo
  function abrirModalNuevo() {
    idEdicion = null;
    error.hide().text('');
    form[0].reset();
    $('#modal-usuario-titulo').text('Registrar Usuario');
    $('#grupo-usuario-estado').hide();
    $('#grupo-usuario-password').show();
    $('#modal-usuario').addClass('visible');
  }

  //Carga los datos del usuario elegido (guardados en data-* de la fila) en el modal de edición
  function abrirModalEditar(boton) {
    idEdicion = boton.data('id');
    error.hide().text('');
    $('#modal-usuario-titulo').text('Editar Usuario');
    $('#usuario-nombre').val(boton.data('nombre'));
    $('#usuario-identificacion').val(boton.data('identificacion'));
    $('#usuario-correo').val(boton.data('correo'));
    $('#usuario-telefono').val(boton.data('telefono'));
    $('#usuario-direccion').val(boton.data('direccion'));
    $('#usuario-rol').val(boton.data('rol'));
    $('#usuario-estado').val(boton.data('estado'));
    $('#grupo-usuario-estado').show();
    $('#grupo-usuario-password').hide();
    $('#modal-usuario').addClass('visible');
  }

  $('#btn-nuevo-usuario').on('click', abrirModalNuevo);

  $('#tabla-usuarios').on('click', '[data-accion="editar"]', function () {
    abrirModalEditar($(this));
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Registra o actualiza el usuario según si hay un id en edición, y recarga para reflejar los datos guardados
  form.on('submit', function (evento) {
    evento.preventDefault();
    error.hide().text('');

    let url = idEdicion
      ? 'index.php?controller=usuarios&action=update&id=' + idEdicion
      : 'index.php?controller=usuarios&action=store';

    $.post(url, form.serialize(), function (data) {
      if (data.response !== '00') {
        error.text(data.message).show();
        return;
      }
      window.location.reload();
    }, 'json');
  });

  $('#buscar-usuario').on('input', filtrar);
  $('#filtro-rol').on('change', filtrar);
  $('#filtro-estado-usuario').on('change', filtrar);

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-usuario') {
    abrirModalNuevo();
  }

});
