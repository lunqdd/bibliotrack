//Perfil del lector: datos personales y cambio de contraseña

(function () {
  'use strict';

  /* Datos del lector mostrados en la vista de perfil. */
  let perfil = {
    nombre: 'Derek Jensen Arguedas',
    correo: 'jjensen20153@ufide.ac.cr',
    telefono: '8878-1234',
    direccion: 'San José, Costa Rica',
    socio: '#4021',
    miembro: '20 Ene 2026',
    prestamos: 3,
    devoluciones: 1
  };

  //Pinta la ficha del lector y conecta los botones del encabezado
  function renderizarPerfil() {
    let contenedor = document.getElementById('perfil-contenido');
    contenedor.innerHTML =
      '<div class="perfil-encabezado">' +
        '<div class="avatar perfil-avatar">DJ</div>' +
        '<div><h2 class="perfil-nombre">' + perfil.nombre + '</h2>' +
        '<p class="perfil-meta">Socio ' + perfil.socio + ' &middot; Miembro desde ' + perfil.miembro + '</p></div>' +
      '</div>' +
      '<div class="detalle-grid">' +
        '<div class="detalle-campo"><div class="detalle-label">Correo Electrónico</div><div class="detalle-valor">' + perfil.correo + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Teléfono</div><div class="detalle-valor">' + perfil.telefono + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Dirección</div><div class="detalle-valor">' + perfil.direccion + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Total Préstamos</div><div class="detalle-valor">' + perfil.prestamos + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Devoluciones</div><div class="detalle-valor">' + perfil.devoluciones + '</div></div>' +
      '</div>' +
      '<div class="perfil-acciones">' +
        '<button class="btn btn-primario" id="btn-editar-perfil"><i class="bi bi-pencil"></i> Editar Perfil</button>' +
        '<button class="btn btn-outline" id="btn-cambiar-clave"><i class="bi bi-lock"></i> Cambiar Contraseña</button>' +
      '</div>';

    document.getElementById('btn-editar-perfil').addEventListener('click', abrirEditarPerfil);
    document.getElementById('btn-cambiar-clave').addEventListener('click', function () {
      document.getElementById('form-clave').reset();
      ocultarErrores();
      document.getElementById('modal-cambiar-clave').classList.add('visible');
    });
  }

  //Carga los datos del lector en el modal de edición
  function abrirEditarPerfil() {
    document.getElementById('perfil-nombre').value = perfil.nombre;
    document.getElementById('perfil-correo').value = perfil.correo;
    document.getElementById('perfil-telefono').value = perfil.telefono;
    document.getElementById('perfil-direccion').value = perfil.direccion;
    document.getElementById('modal-editar-perfil').classList.add('visible');
  }

  //Persiste los cambios del formulario en el objeto y vuelve a pintar la ficha
  window.guardarPerfil = function () {
    let nombre = document.getElementById('perfil-nombre').value.trim();
    let correo = document.getElementById('perfil-correo').value.trim();
    if (!nombre || !correo) return;

    perfil.nombre = nombre;
    perfil.correo = correo;
    perfil.telefono = document.getElementById('perfil-telefono').value.trim();
    perfil.direccion = document.getElementById('perfil-direccion').value.trim();

    cerrarModal('modal-editar-perfil');
    renderizarPerfil();
  };

  //Restablece el estado visual de los campos y oculta los mensajes de error
  function ocultarErrores() {
    let errores = document.querySelectorAll('#form-clave .mensaje-error');
    errores.forEach(function (el) { el.style.display = 'none'; });
    let campos = document.querySelectorAll('#form-clave input');
    campos.forEach(function (el) { el.classList.remove('campo-invalido'); });
  }

  //Marca un campo como inválido y muestra el mensaje asociado
  function mostrarError(campoId, errorId, mensaje) {
    document.getElementById(campoId).classList.add('campo-invalido');
    let errorEl = document.getElementById(errorId);
    errorEl.textContent = mensaje;
    errorEl.style.display = 'block';
  }

  //Nota: la validación visual no reemplaza la verificación que hará PHP
  window.cambiarClave = function () {
    ocultarErrores();
    let actual = document.getElementById('clave-actual').value;
    let nueva = document.getElementById('clave-nueva').value;
    let confirmar = document.getElementById('clave-confirmar').value;
    let valido = true;

    if (actual.length < 1) {
      mostrarError('clave-actual', 'error-clave-actual', 'La contraseña actual es obligatoria.');
      valido = false;
    }
    if (nueva.length < 8) {
      mostrarError('clave-nueva', 'error-clave-nueva', 'La nueva contraseña debe tener al menos 8 caracteres.');
      valido = false;
    }
    if (nueva !== confirmar) {
      mostrarError('clave-confirmar', 'error-clave-confirmar', 'Las contraseñas no coinciden.');
      valido = false;
    }

    if (valido) {
      cerrarModal('modal-cambiar-clave');
    }
  };

  window.cerrarModal = function (id) { document.getElementById(id).classList.remove('visible'); };

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      window.cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  document.getElementById('btn-guardar-perfil').addEventListener('click', window.guardarPerfil);
  document.getElementById('btn-cambiar-clave-modal').addEventListener('click', window.cambiarClave);

  renderizarPerfil();

})();
