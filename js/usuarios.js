//Gestión de usuarios: lista, filtros y modales

(function () {
  'use strict';

//Datos de muestra. rol/estado guardan el mismo valor que el ENUM de
//usuarios en la base de datos (admin/lector, activo/inactivo); el
//texto bonito para mostrar se arma aparte con etiquetaRol/etiquetaEstado

  let usuarios = [
    { nombre: 'Luna Delgado Durango', id: 'ID-100034', correo: 'ldelgado00034@ufide.ac.cr', telefono: '8812-3456', rol: 'admin', estado: 'activo', fecha: 'Registrado el 15 Ene, 2026', color: '#4b3621', direccion: 'Heredia, Costa Rica' },
    { nombre: 'Abby Chavarría Bolaños', id: 'ID-700596', correo: 'achavarria70596@ufide.ac.cr', telefono: '8834-5678', rol: 'lector', estado: 'activo', fecha: 'Registrado el 15 Ene, 2026', color: '#725a42', direccion: 'Heredia, Costa Rica' },
    { nombre: 'María Peña García', id: 'ID-300226', correo: 'mpena30226@ufide.ac.cr', telefono: '8856-7890', rol: 'lector', estado: 'activo', fecha: 'Registrado el 18 Ene, 2026', color: '#59422c', direccion: 'Alajuela, Costa Rica' },
    { nombre: 'Derek Jensen Arguedas', id: 'ID-200153', correo: 'jjensen20153@ufide.ac.cr', telefono: '8878-1234', rol: 'lector', estado: 'activo', fecha: 'Registrado el 20 Ene, 2026', color: '#354a52', direccion: 'San José, Costa Rica' }
  ];

  //Traduce el valor guardado (igual al ENUM de la BD) al texto que ve el usuario
  function etiquetaRol(rol) { return rol === 'admin' ? 'Administrador' : 'Lector'; }
  function etiquetaEstado(estado) { return estado === 'activo' ? 'Activo' : 'Inactivo'; }

  //Identificador del usuario en curso dentro del modal
  let idEdicion = '';

  //Indicadores globales de usuarios registrados
  let statsUsuarios = [
    { icono: 'bi-people', label: 'Total Usuarios', valor: '4', trend: 'Demo' },
    { icono: 'bi-person-check', label: 'Usuarios Activos', valor: '4', trend: '100%' },
    { icono: 'bi-bookmark-check', label: 'Con Préstamos', valor: '3', trend: 'Lectores activos' },
    { icono: 'bi-person-plus', label: 'Nuevos Este Mes', valor: '0', trend: 'Sin registros' }
  ];

  //Construye las tarjetas de resumen de usuarios
  function renderizarStats() {
    let contenedor = document.getElementById('stats-usuarios');
    statsUsuarios.forEach(function (s) {
      let card = document.createElement('div');
      card.className = 'stat-card';
      card.innerHTML =
        '<div class="stat-header"><i class="bi ' + s.icono + ' stat-icono"></i><span class="stat-trend">' + s.trend + '</span></div>' +
        '<p class="stat-label">' + s.label + '</p><p class="stat-valor">' + s.valor + '</p>';
      contenedor.appendChild(card);
    });
  }

  //Puebla los selectores de rol y estado con los mismos valores del ENUM
  function cargarFiltros() {
    let selectRol = document.getElementById('filtro-rol');
    ['admin', 'lector'].forEach(function (r) {
      let o = document.createElement('option'); o.value = r; o.textContent = etiquetaRol(r); selectRol.appendChild(o);
    });
    let selectEstado = document.getElementById('filtro-estado-usuario');
    ['activo', 'inactivo'].forEach(function (e) {
      let o = document.createElement('option'); o.value = e; o.textContent = etiquetaEstado(e); selectEstado.appendChild(o);
    });
  }

  //Obtiene las iniciales del nombre para el avatar de la tabla
  function iniciales(nombre) {
    return nombre.split(' ').map(function (n) { return n[0]; }).join('').substring(0, 2).toUpperCase();
  }

  //Pinta la tabla de usuarios con la lista recibida
  function renderizarTabla(lista) {
    let tbody = document.getElementById('tabla-usuarios');
    tbody.innerHTML = '';
    lista.forEach(function (u) {
      let tr = document.createElement('tr');
      tr.innerHTML =
        '<td><div class="celda-usuario"><div class="avatar-tabla" style="background-color:' + u.color + '">' + iniciales(u.nombre) + '</div><div><span class="nombre-usuario">' + u.nombre + '</span><br><span class="fecha-registro">' + u.fecha + '</span></div></div></td>' +
        '<td>' + u.id + '</td><td>' + u.correo + '</td>' +
        '<td><span class="badge badge-oscuro">' + etiquetaRol(u.rol) + '</span></td>' +
        '<td>' +
          '<span class="badge ' + (u.estado === 'activo' ? 'badge-exito' : 'badge-error') + '"><i class="bi bi-circle-fill" style="font-size:0.4rem"></i> ' + etiquetaEstado(u.estado) + '</span>' +
          ' <button class="btn-icono" title="Ver" data-accion="ver" data-id="' + u.id + '"><i class="bi bi-eye"></i></button>' +
          '<button class="btn-icono" title="Editar" data-accion="editar" data-id="' + u.id + '"><i class="bi bi-pencil"></i></button>' +
        '</td>';
      tbody.appendChild(tr);
    });
    document.getElementById('info-paginacion-usuarios').textContent = 'Mostrando 1 a ' + lista.length + ' de ' + usuarios.length + ' usuarios';
  }

  //Filtra los usuarios por texto, rol y estado
  function filtrar() {
    let busqueda = document.getElementById('buscar-usuario').value.toLowerCase();
    let rol = document.getElementById('filtro-rol').value;
    let estado = document.getElementById('filtro-estado-usuario').value;
    let resultado = usuarios.filter(function (u) {
      return u.nombre.toLowerCase().indexOf(busqueda) !== -1 && (!rol || u.rol === rol) && (!estado || u.estado === estado);
    });
    renderizarTabla(resultado);
  }

  //Dibuja los botones del paginador de usuarios
  function renderizarPaginacion() {
    let contenedor = document.getElementById('paginacion-usuarios');
    ['<', '1', '>'].forEach(function (p, i) {
      let btn = document.createElement('button'); btn.textContent = p;
      if (i === 1) btn.className = 'activo';
      contenedor.appendChild(btn);
    });
  }

  window.abrirModalUsuario = function () {
    idEdicion = '';
    document.getElementById('modal-usuario-titulo').textContent = 'Registrar Usuario';
    document.getElementById('form-usuario').reset();
    document.getElementById('modal-usuario').classList.add('visible');
  };

  //Devuelve el usuario que coincide con el identificador
  function buscarUsuario(id) {
    return usuarios.find(function (usuario) { return usuario.id === id; });
  }

  //Carga los datos del usuario en el modal de edición
  function editarUsuario(id) {
    idEdicion = id;
    let u = buscarUsuario(id);
    if (!u) return;
    document.getElementById('modal-usuario-titulo').textContent = 'Editar Usuario';
    document.getElementById('usuario-nombre').value = u.nombre;
    document.getElementById('usuario-id').value = u.id;
    document.getElementById('usuario-correo').value = u.correo;
    document.getElementById('usuario-telefono').value = u.telefono;
    document.getElementById('usuario-rol').value = u.rol;
    document.getElementById('usuario-direccion').value = u.direccion;
    document.getElementById('modal-usuario').classList.add('visible');
  }

  //Muestra los datos completos del usuario en el modal de detalle
  function verDetalleUsuario(id) {
    let u = buscarUsuario(id);
    if (!u) return;
    let contenido = document.getElementById('detalle-usuario-contenido');
    contenido.innerHTML =
      '<div class="detalle-grid">' +
        '<div class="detalle-campo"><div class="detalle-label">Nombre</div><div class="detalle-valor">' + u.nombre + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Identificación</div><div class="detalle-valor">' + u.id + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Correo</div><div class="detalle-valor">' + u.correo + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Teléfono</div><div class="detalle-valor">' + u.telefono + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Rol</div><div class="detalle-valor"><span class="badge badge-oscuro">' + etiquetaRol(u.rol) + '</span></div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Estado</div><div class="detalle-valor"><span class="badge ' + (u.estado === 'activo' ? 'badge-exito' : 'badge-error') + '">' + etiquetaEstado(u.estado) + '</span></div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Dirección</div><div class="detalle-valor">' + u.direccion + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Registro</div><div class="detalle-valor">' + u.fecha + '</div></div>' +
      '</div>';
    document.getElementById('modal-detalle-usuario').classList.add('visible');
  }

  window.guardarUsuario = function () {
    let datos = {
      nombre: document.getElementById('usuario-nombre').value,
      id: document.getElementById('usuario-id').value,
      correo: document.getElementById('usuario-correo').value,
      telefono: document.getElementById('usuario-telefono').value,
      rol: document.getElementById('usuario-rol').value || 'lector',
      direccion: document.getElementById('usuario-direccion').value,
      estado: 'activo',
      fecha: 'Registrado hoy',
      color: '#4b3621'
    };
    if (!datos.nombre || !datos.correo) return;
    if (idEdicion) {
      let usuarioActual = buscarUsuario(idEdicion);
      if (!usuarioActual) return;
      datos.id = idEdicion;
      datos.estado = usuarioActual.estado;
      datos.fecha = usuarioActual.fecha;
      usuarios[usuarios.indexOf(usuarioActual)] = datos;
    } else {
      datos.id = document.getElementById('usuario-id').value || 'ID-DEMO-' + String(usuarios.length + 1).padStart(3, '0');
      usuarios.push(datos);
    }
    cerrarModal('modal-usuario');
    filtrar();
  };

  window.cerrarModal = function (id) { document.getElementById(id).classList.remove('visible'); };

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      window.cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  document.getElementById('btn-guardar-usuario').addEventListener('click', window.guardarUsuario);

  //Despacha los botones de ver y editar de cada fila de la tabla
  document.getElementById('tabla-usuarios').addEventListener('click', function (e) {
    let boton = e.target.closest('[data-accion]');
    if (!boton) return;
    let id = boton.getAttribute('data-id');
    if (boton.getAttribute('data-accion') === 'ver') verDetalleUsuario(id);
    if (boton.getAttribute('data-accion') === 'editar') editarUsuario(id);
  });

  //Conecta el botón de la cabecera para registrar un usuario nuevo
  document.querySelector('.pagina-acciones .btn').addEventListener('click', window.abrirModalUsuario);

  document.getElementById('buscar-usuario').addEventListener('input', filtrar);
  document.getElementById('filtro-rol').addEventListener('change', filtrar);
  document.getElementById('filtro-estado-usuario').addEventListener('change', filtrar);

  renderizarStats();
  cargarFiltros();
  renderizarTabla(usuarios);
  renderizarPaginacion();

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-usuario') {
    window.abrirModalUsuario();
  }

})();
