// Gestión de usuarios: filtros, altas y ediciones contra el servidor por AJAX.

$(function () {

  let tabla = $('#tabla-usuarios');
  let infoPaginacion = $('#info-paginacion-usuarios');
  let modalUsuario = $('#modal-usuario');
  let modalDetalle = $('#modal-detalle-usuario');
  let mensajeUsuario = $('#mensaje-error-usuario');
  let temporizadorBusqueda = null;

  function etiquetaRol(rol) { return rol === 'admin' ? 'Administrador' : 'Lector'; }
  function etiquetaEstado(estado) { return estado === 'activo' ? 'Activo' : 'Inactivo'; }

  function filaUsuario(usuario) {
    return '<tr>'
      + '<td><div class="celda-usuario">'
      + '<div class="avatar-tabla" style="background-color:' + usuario.color + '">' + usuario.iniciales + '</div>'
      + '<div><span class="nombre-usuario">' + usuario.nombre_completo + '</span><br><span class="fecha-registro">' + usuario.fecha_registro_texto + '</span></div>'
      + '</div></td>'
      + '<td>' + usuario.identificacion + '</td>'
      + '<td>' + usuario.correo + '</td>'
      + '<td><span class="badge badge-oscuro">' + etiquetaRol(usuario.rol) + '</span></td>'
      + '<td class="acciones-celda">'
      + '<span class="badge ' + (usuario.estado === 'activo' ? 'badge-exito' : 'badge-error') + '"><i class="bi bi-circle-fill" style="font-size:0.4rem"></i> ' + etiquetaEstado(usuario.estado) + '</span>'
      + ' <button class="btn-icono" title="Ver" data-accion="ver" data-id="' + usuario.usuario_id + '"><i class="bi bi-eye"></i></button>'
      + '<button class="btn-icono" title="Editar" data-accion="editar" data-id="' + usuario.usuario_id + '"><i class="bi bi-pencil"></i></button>'
      + '</td></tr>';
  }

  //Pide el listado filtrado y repinta la tabla sin recargar la página.
  function buscarUsuarios() {
    $.get('index.php?controller=usuario&action=buscarUsuarios', {
      q: $('#buscar-usuario').val(),
      rol: $('#filtro-rol').val(),
      estado: $('#filtro-estado-usuario').val(),
    }, function (data) {
      let lista = data.usuarios || [];

      if (!lista.length) {
        tabla.html('<tr><td colspan="5">No se encontraron usuarios con ese criterio.</td></tr>');
        infoPaginacion.text('0 usuarios encontrados');
        return;
      }

      tabla.empty();
      lista.forEach(function (usuario) { tabla.append(filaUsuario(usuario)); });
      infoPaginacion.text(lista.length + (lista.length === 1 ? ' usuario encontrado' : ' usuarios encontrados'));
    }, 'json');
  }

  $('#buscar-usuario').on('input', function () {
    clearTimeout(temporizadorBusqueda);
    temporizadorBusqueda = setTimeout(buscarUsuarios, 300);
  });
  $('#filtro-rol, #filtro-estado-usuario').on('change', buscarUsuarios);

  function limpiarFormulario() {
    $('#form-usuario')[0].reset();
    $('#usuario-db-id').val('');
    mensajeUsuario.hide().text('');
  }

  function abrirModalNuevo() {
    limpiarFormulario();
    $('#modal-usuario-titulo').text('Registrar Usuario');
    $('#nota-password-usuario').show();
    modalUsuario.addClass('visible');
  }

  //Trae los datos del usuario y precarga el formulario de edición.
  function abrirModalEdicion(id) {
    $.get('index.php?controller=usuario&action=edit', { id: id }, function (data) {
      if (data.response !== '00') return;
      let usuario = data.usuario;
      limpiarFormulario();
      $('#modal-usuario-titulo').text('Editar Usuario');
      $('#usuario-db-id').val(usuario.usuario_id);
      $('#usuario-nombre').val(usuario.nombre_completo);
      $('#usuario-identificacion').val(usuario.identificacion);
      $('#usuario-correo').val(usuario.correo);
      $('#usuario-telefono').val(usuario.telefono);
      $('#usuario-rol').val(usuario.rol);
      $('#usuario-direccion').val(usuario.direccion);
      $('#nota-password-usuario').hide();
      modalUsuario.addClass('visible');
    }, 'json');
  }

  //Muestra los datos del usuario en modo lectura.
  function verDetalleUsuario(id) {
    $.get('index.php?controller=usuario&action=edit', { id: id }, function (data) {
      if (data.response !== '00') return;
      let usuario = data.usuario;
      let contenido =
        '<div class="detalle-grid">'
        + '<div class="detalle-campo"><div class="detalle-label">Nombre</div><div class="detalle-valor">' + usuario.nombre_completo + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Identificación</div><div class="detalle-valor">' + usuario.identificacion + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Correo</div><div class="detalle-valor">' + usuario.correo + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Teléfono</div><div class="detalle-valor">' + (usuario.telefono || 'Sin dato') + '</div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Rol</div><div class="detalle-valor"><span class="badge badge-oscuro">' + etiquetaRol(usuario.rol) + '</span></div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Estado</div><div class="detalle-valor"><span class="badge ' + (usuario.estado === 'activo' ? 'badge-exito' : 'badge-error') + '">' + etiquetaEstado(usuario.estado) + '</span></div></div>'
        + '<div class="detalle-campo"><div class="detalle-label">Dirección</div><div class="detalle-valor">' + (usuario.direccion || 'Sin dato') + '</div></div>'
        + '</div>';
      $('#detalle-usuario-contenido').html(contenido);
      modalDetalle.addClass('visible');
    }, 'json');
  }

  //Guarda un usuario nuevo o actualiza los datos de uno existente.
  $('#btn-guardar-usuario').on('click', function () {
    mensajeUsuario.hide().text('');

    let id = $('#usuario-db-id').val();
    let datos = {
      nombre_completo: $('#usuario-nombre').val().trim(),
      identificacion: $('#usuario-identificacion').val().trim(),
      correo: $('#usuario-correo').val().trim(),
      telefono: $('#usuario-telefono').val().trim(),
      rol: $('#usuario-rol').val(),
      direccion: $('#usuario-direccion').val().trim(),
    };

    if (!datos.nombre_completo || !datos.identificacion || !datos.correo) {
      mensajeUsuario.text('El nombre, la identificación y el correo son obligatorios.').show();
      return;
    }

    let accion = id ? 'update' : 'store';
    let url = 'index.php?controller=usuario&action=' + accion + (id ? '&id=' + id : '');

    $.post(url, datos, function (data) {
      if (data.response !== '00') {
        mensajeUsuario.text(data.message).show();
        return;
      }
      modalUsuario.removeClass('visible');
      buscarUsuarios();
    }, 'json');
  });

  //Despacha los botones de ver y editar de cada fila
  tabla.on('click', '[data-accion]', function () {
    let boton = $(this);
    let id = boton.data('id');
    let accion = boton.data('accion');
    if (accion === 'ver') verDetalleUsuario(id);
    if (accion === 'editar') abrirModalEdicion(id);
  });

  $('#btn-nuevo-usuario').on('click', abrirModalNuevo);

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-usuario') {
    abrirModalNuevo();
  }

});