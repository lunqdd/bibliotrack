//Perfil administrativo

(function () {
  'use strict';

  //Datos de la cuenta administrativa mostrados en la ficha
  let perfil = {
    nombre: 'Luna Delgado Durango',
    correo: 'ldelgado00034@ufide.ac.cr',
    rol: 'Administradora',
    telefono: '8812-3456',
    direccion: 'Heredia, Costa Rica',
    ultimoAcceso: '29 Jun 2026'
  };

  //Pinta la ficha del administrador y conecta los botones del encabezado
  function renderizarPerfil() {
    let contenedor = document.getElementById('perfil-admin-contenido');
    contenedor.innerHTML =
      '<div class="perfil-encabezado">' +
        '<div class="avatar perfil-avatar">LD</div>' +
        '<div><h2 class="perfil-nombre">' + perfil.nombre + '</h2>' +
        '<p class="perfil-meta">' + perfil.correo + '</p>' +
        '<span class="badge badge-oscuro perfil-admin-badge">' + perfil.rol + '</span></div>' +
      '</div>' +
      '<div class="detalle-grid">' +
        '<div class="detalle-campo"><div class="detalle-label">Correo Electrónico</div><div class="detalle-valor">' + perfil.correo + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Teléfono</div><div class="detalle-valor">' + perfil.telefono + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Dirección</div><div class="detalle-valor">' + perfil.direccion + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Último acceso</div><div class="detalle-valor">' + perfil.ultimoAcceso + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Permisos</div><div class="detalle-valor">Libros, usuarios, préstamos, inventario y reportes</div></div>' +
      '</div>' +
      '<div class="perfil-acciones">' +
        '<button class="btn btn-primario" id="btn-editar-perfil-admin"><i class="bi bi-pencil"></i> Editar Perfil</button>' +
        '<button class="btn btn-outline" id="btn-cambiar-clave-admin"><i class="bi bi-lock"></i> Cambiar Contraseña</button>' +
      '</div>';

    document.getElementById('btn-editar-perfil-admin').addEventListener('click', abrirEditarPerfil);
    document.getElementById('btn-cambiar-clave-admin').addEventListener('click', abrirCambioClave);
  }

  //Carga los datos del administrador en el modal de edición
  function abrirEditarPerfil() {
    document.getElementById('perfil-admin-nombre').value = perfil.nombre;
    document.getElementById('perfil-admin-correo').value = perfil.correo;
    document.getElementById('perfil-admin-telefono').value = perfil.telefono;
    document.getElementById('perfil-admin-direccion').value = perfil.direccion;
    document.getElementById('modal-editar-perfil-admin').classList.add('visible');
  }

  //Persiste los cambios del formulario en el objeto y vuelve a pintar la ficha
  function guardarPerfil() {
    let nombre = document.getElementById('perfil-admin-nombre').value.trim();
    let correo = document.getElementById('perfil-admin-correo').value.trim();
    if (!nombre || !correo) return;

    perfil.nombre = nombre;
    perfil.correo = correo;
    perfil.telefono = document.getElementById('perfil-admin-telefono').value.trim();
    perfil.direccion = document.getElementById('perfil-admin-direccion').value.trim();

    cerrarModal('modal-editar-perfil-admin');
    renderizarPerfil();
  }

  //Prepara el modal de cambio de contraseña
  function abrirCambioClave() {
    document.getElementById('form-clave-admin').reset();
    ocultarErrores();
    document.getElementById('modal-cambiar-clave-admin').classList.add('visible');
  }

  //Restablece el estado visual de los campos y oculta los mensajes de error
  function ocultarErrores() {
    let errores = document.querySelectorAll('#form-clave-admin .mensaje-error');
    errores.forEach(function (el) { el.style.display = 'none'; });
    let campos = document.querySelectorAll('#form-clave-admin input');
    campos.forEach(function (el) { el.classList.remove('campo-invalido'); });
  }

  //Marca un campo como inválido y muestra el mensaje asociado
  function mostrarError(campoId, errorId, mensaje) {
    document.getElementById(campoId).classList.add('campo-invalido');
    let errorEl = document.getElementById(errorId);
    errorEl.textContent = mensaje;
    errorEl.style.display = 'block';
  }

  //Valida los campos del cambio de contraseña antes de cerrar el modal
  function cambiarClave() {
    ocultarErrores();
    let actual = document.getElementById('clave-admin-actual').value;
    let nueva = document.getElementById('clave-admin-nueva').value;
    let confirmar = document.getElementById('clave-admin-confirmar').value;
    let valido = true;

    if (actual.length < 1) {
      mostrarError('clave-admin-actual', 'error-clave-admin-actual', 'La contraseña actual es obligatoria.');
      valido = false;
    }
    if (nueva.length < 8) {
      mostrarError('clave-admin-nueva', 'error-clave-admin-nueva', 'La nueva contraseña debe tener al menos 8 caracteres.');
      valido = false;
    }
    if (nueva !== confirmar) {
      mostrarError('clave-admin-confirmar', 'error-clave-admin-confirmar', 'Las contraseñas no coinciden.');
      valido = false;
    }

    if (valido) {
      cerrarModal('modal-cambiar-clave-admin');
    }
  }

  //Cierra el modal indicado
  function cerrarModal(id) {
    document.getElementById(id).classList.remove('visible');
  }

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  document.getElementById('btn-guardar-perfil-admin').addEventListener('click', guardarPerfil);
  document.getElementById('btn-cambiar-clave-admin-modal').addEventListener('click', cambiarClave);

  //Cierra el modal correspondiente al hacer clic sobre el fondo
  document.querySelectorAll('.modal-overlay').forEach(function (modal) {
    modal.addEventListener('click', function (evento) {
      if (evento.target === modal) cerrarModal(modal.id);
    });
  });

  renderizarPerfil();
})();
