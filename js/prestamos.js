//Préstamos y devoluciones: historial, filtros y formularios

(function () {
  'use strict';

// Datos de muestra. fechaProgramada = prestamos.fecha_devolucion_programada
//(siempre existe). fechaDevolucionReal = devoluciones.fecha_devolucion
//(solo existe una vez que el préstamo se devolvió: son dos columnas de
//dos tablas distintas, nunca el mismo dato). 

  let prestamos = [
    { id: 'PRE-001', nombre: 'Derek Jensen', idUsuario: 'ID-200153', libro: 'Cien Años de Soledad', isbn: '978-0307350433', fechaPrestamo: '12 Jun 2026', fechaProgramada: '26 Jun 2026', fechaDevolucionReal: '25 Jun 2026', estadoEjemplar: 'buen_estado', notasDevolucion: '', estado: 'DEVUELTO', color: '#354a52' },
    { id: 'PRE-002', nombre: 'Abby Chavarría', idUsuario: 'ID-700596', libro: 'Breve Historia del Tiempo', isbn: '978-0553109580', fechaPrestamo: '18 Jun 2026', fechaProgramada: '02 Jul 2026', fechaDevolucionReal: null, estadoEjemplar: null, notasDevolucion: '', estado: 'EN CURSO', color: '#725a42' },
    { id: 'PRE-003', nombre: 'María Peña', idUsuario: 'ID-300226', libro: 'Historia de Roma', isbn: '978-8497593151', fechaPrestamo: '05 Jun 2026', fechaProgramada: '19 Jun 2026', fechaDevolucionReal: null, estadoEjemplar: null, notasDevolucion: '', estado: 'VENCIDO', color: '#59422c' },
    { id: 'PRE-004', nombre: 'Derek Jensen', idUsuario: 'ID-200153', libro: 'Don Quijote de la Mancha', isbn: '978-8420412146', fechaPrestamo: '24 Jun 2026', fechaProgramada: '08 Jul 2026', fechaDevolucionReal: null, estadoEjemplar: null, notasDevolucion: '', estado: 'EN CURSO', color: '#354a52' },
    { id: 'PRE-005', nombre: 'Derek Jensen', idUsuario: 'ID-200153', libro: 'El Principito', isbn: '978-0156012195', fechaPrestamo: '20 Jun 2026', fechaProgramada: '04 Jul 2026', fechaDevolucionReal: null, estadoEjemplar: null, notasDevolucion: '', estado: 'EN CURSO', color: '#354a52' },
    { id: 'PRE-006', nombre: 'Abby Chavarría', idUsuario: 'ID-700596', libro: 'Breve Historia del Tiempo', isbn: '978-0553109580', fechaPrestamo: '22 Jun 2026', fechaProgramada: '06 Jul 2026', fechaDevolucionReal: null, estadoEjemplar: null, notasDevolucion: '', estado: 'EN CURSO', color: '#725a42' },
    { id: 'PRE-007', nombre: 'Abby Chavarría', idUsuario: 'ID-700596', libro: 'Breve Historia del Tiempo', isbn: '978-0553109580', fechaPrestamo: '26 Jun 2026', fechaProgramada: '10 Jul 2026', fechaDevolucionReal: null, estadoEjemplar: null, notasDevolucion: '', estado: 'EN CURSO', color: '#725a42' }
  ];

  //Traduce el value del ENUM de devoluciones.estado_ejemplar a texto
  function etiquetaEstadoEjemplar(valor) {
    if (valor === 'buen_estado') return 'Buen estado';
    if (valor === 'dano_menor') return 'Daño menor';
    if (valor === 'requiere_reparacion') return 'Requiere reparación';
    return '';
  }

  //Opciones mostradas en los select de los formularios de préstamo y devolución, idUsuario y color usan los mismos valores reales que usuarios.js
  let usuariosDisponibles = [
    { nombre: 'Derek Jensen', idUsuario: 'ID-200153', color: '#354a52' },
    { nombre: 'Abby Chavarría', idUsuario: 'ID-700596', color: '#725a42' },
    { nombre: 'María Peña', idUsuario: 'ID-300226', color: '#59422c' },
    { nombre: 'Luna Delgado', idUsuario: 'ID-100034', color: '#4b3621' }
  ];

  let librosDisponibles = ['Cien Años de Soledad', 'Historia de Roma', 'El arte del diseño', 'El Principito', 'Don Quijote de la Mancha', 'Sapiens'];

  //Mismos ISBN que libros.js, para no inventar uno al registrar un préstamo nuevo
  let isbnPorLibro = {
    'Cien Años de Soledad': '978-0307350433',
    'El Principito': '978-0156012195',
    'Historia de Roma': '978-8497593151',
    'El arte del diseño': '978-0141984254',
    'Don Quijote de la Mancha': '978-8420412146',
    'Sapiens': '978-0062316097'
  };

  //Indicadores globales de préstamos y devoluciones
  let statsPrestamos = [
    { icono: 'bi-book', label: 'Préstamos Activos', valor: '6', trend: '5 en curso' },
    { icono: 'bi-arrow-return-left', label: 'Devoluciones Registradas', valor: '1' },
    { icono: 'bi-exclamation-triangle', label: 'Libros Vencidos', valor: '1', tipo: 'error' },
    { icono: 'bi-graph-up', label: 'Tasa de Retorno', valor: '14%' }
  ];

  //Construye las tarjetas de resumen de préstamos
  function renderizarStats() {
    let contenedor = document.getElementById('stats-prestamos');
    statsPrestamos.forEach(function (s) {
      let card = document.createElement('div');
      card.className = 'stat-card' + (s.tipo === 'error' ? ' stat-error' : '');
      card.innerHTML =
        '<div class="stat-header"><i class="bi ' + s.icono + ' stat-icono"></i>' +
        (s.trend ? '<span class="stat-trend">' + s.trend + '</span>' : '') + '</div>' +
        '<p class="stat-label">' + s.label + '</p><p class="stat-valor">' + s.valor + '</p>';
      contenedor.appendChild(card);
    });
  }

  //Mapea el estado del préstamo a la clase de badge correspondiente
  function claseBadge(estado) {
    if (estado === 'DEVUELTO') return 'badge-exito';
    if (estado === 'EN CURSO') return 'badge-alerta';
    return 'badge-error';
  }

  //Obtiene las iniciales del nombre para el avatar de la tabla
  function iniciales(nombre) {
    return nombre.split(' ').map(function (n) { return n[0]; }).join('').substring(0, 2).toUpperCase();
  }

  //Puebla el selector de estado con los valores posibles
  function cargarFiltros() {
    let select = document.getElementById('filtro-estado-prestamo');
    ['DEVUELTO', 'EN CURSO', 'VENCIDO'].forEach(function (e) {
      let o = document.createElement('option'); o.value = e; o.textContent = e; select.appendChild(o);
    });
  }

  //Llena los select de usuarios, libros y préstamos en curso
  function cargarSelectsModales() {
    let selectUsuario = document.getElementById('prestamo-usuario');
    usuariosDisponibles.forEach(function (u) {
      let o = document.createElement('option'); o.value = u.nombre; o.textContent = u.nombre; selectUsuario.appendChild(o);
    });
    let selectLibro = document.getElementById('prestamo-libro');
    librosDisponibles.forEach(function (l) {
      let o = document.createElement('option'); o.value = l; o.textContent = l; selectLibro.appendChild(o);
    });
    let selectDevolucion = document.getElementById('devolucion-prestamo');
    prestamos.filter(function (p) { return p.estado !== 'DEVUELTO'; }).forEach(function (p) {
      let o = document.createElement('option'); o.value = p.id; o.textContent = p.nombre + ' — ' + p.libro; selectDevolucion.appendChild(o);
    });
  }

  //Pinta la tabla de préstamos con la lista recibida
  function renderizarTabla(lista) {
    let tbody = document.getElementById('tabla-prestamos');
    tbody.innerHTML = '';
    lista.forEach(function (p) {
      let tr = document.createElement('tr');
      let celdaFecha = p.fechaDevolucionReal
        ? p.fechaDevolucionReal
        : '<span class="isbn-texto">Programada: ' + p.fechaProgramada + '</span>';
      tr.innerHTML =
        '<td><div class="celda-usuario"><div class="avatar-tabla" style="background-color:' + p.color + '">' + iniciales(p.nombre) + '</div><div><span class="nombre-usuario">' + p.nombre + '</span><br><span class="id-usuario">ID: ' + p.idUsuario + '</span></div></div></td>' +
        '<td><div>' + p.libro + '<br><span class="isbn-texto">ISBN: ' + p.isbn + '</span></div></td>' +
        '<td>' + p.fechaPrestamo + '</td><td>' + celdaFecha + '</td>' +
        '<td><span class="badge ' + claseBadge(p.estado) + '">' + p.estado + '</span></td>' +
        '<td class="acciones-celda">' +
          '<button class="btn-icono" title="Ver detalle" data-accion="ver" data-id="' + p.id + '"><i class="bi bi-eye"></i></button>' +
        '</td>';
      tbody.appendChild(tr);
    });
    document.getElementById('info-paginacion-prestamos').textContent = 'Mostrando 1 a ' + lista.length + ' de ' + prestamos.length + ' registros';
  }

  //Filtra los préstamos según el estado seleccionado
  function filtrar() {
    let estado = document.getElementById('filtro-estado-prestamo').value;
    renderizarTabla(prestamos.filter(function (p) { return !estado || p.estado === estado; }));
  }

  //Dibuja los botones del paginador de préstamos
  function renderizarPaginacion() {
    let contenedor = document.getElementById('paginacion-prestamos');
    ['<', '1', '>'].forEach(function (p, i) {
      let btn = document.createElement('button'); btn.textContent = p;
      if (i === 1) btn.className = 'activo';
      contenedor.appendChild(btn);
    });
  }

  //Devuelve el préstamo que coincide con el identificador
  function buscarPrestamo(id) {
    return prestamos.find(function (prestamo) { return prestamo.id === id; });
  }

  //Muestra los datos completos del préstamo en el modal de detalle
  function verDetallePrestamo(id) {
    let p = buscarPrestamo(id);
    if (!p) return;
    let contenido = document.getElementById('detalle-prestamo-contenido');
    contenido.innerHTML =
      '<div class="detalle-grid">' +
        '<div class="detalle-campo"><div class="detalle-label">Usuario</div><div class="detalle-valor">' + p.nombre + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">ID</div><div class="detalle-valor">' + p.idUsuario + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Libro</div><div class="detalle-valor">' + p.libro + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">ISBN</div><div class="detalle-valor">' + p.isbn + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Fecha Préstamo</div><div class="detalle-valor">' + p.fechaPrestamo + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Fecha Devolución Programada</div><div class="detalle-valor">' + p.fechaProgramada + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Fecha Devolución Real</div><div class="detalle-valor">' + (p.fechaDevolucionReal || '—') + '</div></div>' +
        '<div class="detalle-campo"><div class="detalle-label">Estado</div><div class="detalle-valor"><span class="badge ' + claseBadge(p.estado) + '">' + p.estado + '</span></div></div>' +
        (p.estadoEjemplar ? '<div class="detalle-campo"><div class="detalle-label">Estado del Ejemplar al Devolver</div><div class="detalle-valor">' + etiquetaEstadoEjemplar(p.estadoEjemplar) + '</div></div>' : '') +
        (p.notasDevolucion ? '<div class="detalle-campo"><div class="detalle-label">Notas de Devolución</div><div class="detalle-valor">' + p.notasDevolucion + '</div></div>' : '') +
      '</div>';
    document.getElementById('modal-detalle-prestamo').classList.add('visible');
  }

  //Prepara el modal para registrar un préstamo nuevo
  function abrirNuevoPrestamo() {
    document.getElementById('form-prestamo').reset();
    document.getElementById('modal-prestamo').classList.add('visible');
  }

  //Registra un préstamo nuevo. Un préstamo ya creado no se "edita": queda fijo a un ejemplar específico, igual que en prestamos.ejemplar_id
  window.guardarPrestamo = function () {
    let nombreUsuario = document.getElementById('prestamo-usuario').value;
    let libro = document.getElementById('prestamo-libro').value;
    if (!nombreUsuario || !libro) return;
    let usuario = usuariosDisponibles.find(function (u) { return u.nombre === nombreUsuario; });
    prestamos.push({
      id: 'PRE-DEMO-' + String(prestamos.length + 1).padStart(3, '0'),
      nombre: usuario.nombre, idUsuario: usuario.idUsuario,
      libro: libro, isbn: isbnPorLibro[libro] || '',
      fechaPrestamo: '30 Jun 2026', fechaProgramada: '14 Jul 2026', fechaDevolucionReal: null,
      estadoEjemplar: null, notasDevolucion: '', estado: 'EN CURSO',
      color: usuario.color
    });
    cerrarModal('modal-prestamo');
    filtrar();
  };

  window.guardarDevolucion = function () {
    let seleccion = document.getElementById('devolucion-prestamo').value;
    let estadoEjemplar = document.getElementById('devolucion-estado').value;
    let notas = document.getElementById('devolucion-notas').value;
    if (!seleccion) return;
    let prestamo = buscarPrestamo(seleccion);
    if (prestamo && prestamo.estado === 'EN CURSO') {
      prestamo.estado = 'DEVUELTO';
      prestamo.fechaDevolucionReal = '30 Jun 2026';
      prestamo.estadoEjemplar = estadoEjemplar || 'buen_estado';
      prestamo.notasDevolucion = notas;
    }
    cerrarModal('modal-devolucion');
    filtrar();
  };

  window.cerrarModal = function (id) { document.getElementById(id).classList.remove('visible'); };

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      window.cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  document.getElementById('btn-guardar-prestamo').addEventListener('click', window.guardarPrestamo);
  document.getElementById('btn-guardar-devolucion').addEventListener('click', window.guardarDevolucion);

  //Despacha el botón de ver detalle de cada fila de la tabla
  document.getElementById('tabla-prestamos').addEventListener('click', function (e) {
    let boton = e.target.closest('[data-accion="ver"]');
    if (!boton) return;
    verDetallePrestamo(boton.getAttribute('data-id'));
  });

  document.getElementById('btn-abrir-devolucion').addEventListener('click', function () {
    document.getElementById('modal-devolucion').classList.add('visible');
  });
  document.getElementById('btn-abrir-prestamo').addEventListener('click', abrirNuevoPrestamo);

  document.getElementById('filtro-estado-prestamo').addEventListener('change', filtrar);

  renderizarStats();
  cargarFiltros();
  cargarSelectsModales();
  renderizarTabla(prestamos);
  renderizarPaginacion();

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#nuevo-prestamo') {
    abrirNuevoPrestamo();
  }

  //Abre el modal de devolución si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-devolucion') {
    document.getElementById('modal-devolucion').classList.add('visible');
  }

})();
