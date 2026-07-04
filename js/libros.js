//Gestión de libros: datos de prueba, filtros y modales

(function () {
  'use strict';

  //Datos de muestra del catálogo de libros. No hay un "estado" por libro:
  //cada copia (ejemplares.estado en la BD) tiene el suyo, así que aquí solo
  //se guarda cuántas copias hay en total y cuántas están disponibles.
  let libros = [
    { id: 'LIB-001', titulo: 'Cien años de soledad', autor: 'Gabriel García Márquez', isbn: '978-0307350433', categoria: 'Novela', editorial: 'Sudamericana', anio: 1967, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780307350433-M.jpg', ejemplares: 5, disponibles: 5 },
    { id: 'LIB-002', titulo: 'Breve historia del tiempo', autor: 'Stephen Hawking', isbn: '978-0553109580', categoria: 'Ciencia', editorial: 'Bantam Books', anio: 1988, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780553109580-M.jpg', ejemplares: 3, disponibles: 0 },
    { id: 'LIB-003', titulo: 'Historia de Roma', autor: 'Indro Montanelli', isbn: '978-8497593151', categoria: 'Historia', editorial: 'Plaza & Janés', anio: 1957, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9788497593151-M.jpg', ejemplares: 2, disponibles: 1 },
    { id: 'LIB-004', titulo: 'El arte del diseño', autor: 'Alice Rawsthorn', isbn: '978-0141984254', categoria: 'Arte', editorial: 'Penguin', anio: 2013, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780141984254-M.jpg', ejemplares: 4, disponibles: 4 },
    { id: 'LIB-005', titulo: 'El Principito', autor: 'Antoine de Saint-Exupéry', isbn: '978-0156012195', categoria: 'Novela', editorial: 'Reynal & Hitchcock', anio: 1943, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780156012195-M.jpg', ejemplares: 8, disponibles: 7 },
    { id: 'LIB-006', titulo: 'Cosmos', autor: 'Carl Sagan', isbn: '978-0345539434', categoria: 'Ciencia', editorial: 'Random House', anio: 1980, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780345539434-M.jpg', ejemplares: 3, disponibles: 0 },
    { id: 'LIB-007', titulo: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', isbn: '978-8420412146', categoria: 'Novela', editorial: 'Francisco de Robles', anio: 1605, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9788420412146-M.jpg', ejemplares: 6, disponibles: 5 },
    { id: 'LIB-008', titulo: 'Sapiens', autor: 'Yuval Noah Harari', isbn: '978-0062316097', categoria: 'Historia', editorial: 'Debate', anio: 2011, portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780062316097-M.jpg', ejemplares: 4, disponibles: 4 }
  ];

  //Identificadores en curso dentro de los modales
  let idEdicion = '';
  let idEliminar = '';

  //Disponibilidad calculada a partir de ejemplares/disponibles, igual que lo haría una consulta SQL sobre la tabla "ejemplares" agrupada por libro
  function disponibilidad(libro) {
    if (libro.disponibles <= 0) return 'AGOTADO';
    if (libro.disponibles < libro.ejemplares) return 'PARCIAL';
    return 'DISPONIBLE';
  }

  //Indicadores globales del catálogo mostrados arriba.
  let statsLibros = [
    { label: 'Títulos Registrados', valor: '8', trend: '' },
    { label: 'Total Ejemplares', valor: '35', trend: '' },
    { label: 'Ejemplares Disponibles', valor: '26', trend: '74% total', trendColor: 'var(--exito)' },
    { label: 'En Reparación', valor: '3', trend: 'Revisión pendiente', trendColor: 'var(--error)', tipo: 'error' }
  ];

  //Llena las tarjetas superiores con los datos generales del catálogo
  function renderizarStats() {
    let contenedor = document.getElementById('stats-libros');
    statsLibros.forEach(function (stat) {
      let card = document.createElement('div');
      card.className = 'stat-card' + (stat.tipo === 'error' ? ' stat-error' : '');
      card.innerHTML =
        '<p class="stat-label">' + stat.label + '</p>' +
        '<p class="stat-valor">' + stat.valor + '</p>' +
        (stat.trend ? '<span class="stat-trend" style="color:' + (stat.trendColor || 'var(--exito)') + '">' + stat.trend + '</span>' : '');
      contenedor.appendChild(card);
    });
  }

  // Agrega en los select las categorías y editoriales que existen en el arreglo.
  function cargarFiltros() {
    let generos = [], editoriales = [];
    libros.forEach(function (libro) {
      if (generos.indexOf(libro.categoria) === -1) generos.push(libro.categoria);
      if (editoriales.indexOf(libro.editorial) === -1) editoriales.push(libro.editorial);
    });
    let selectGenero = document.getElementById('filtro-genero');
    generos.forEach(function (g) { let o = document.createElement('option'); o.value = g; o.textContent = g; selectGenero.appendChild(o); });
    let selectEditorial = document.getElementById('filtro-editorial');
    editoriales.forEach(function (e) { let o = document.createElement('option'); o.value = e; o.textContent = e; selectEditorial.appendChild(o); });
    let selectEstado = document.getElementById('filtro-estado');
    ['DISPONIBLE', 'PARCIAL', 'AGOTADO'].forEach(function (e) {
      let o = document.createElement('option'); o.value = e; o.textContent = e; selectEstado.appendChild(o);
    });
  }

  // Mapea la disponibilidad calculada a la clase de badge correspondiente.
  function claseBadge(estado) {
    if (estado === 'DISPONIBLE') return 'badge-exito';
    if (estado === 'PARCIAL') return 'badge-alerta';
    return 'badge-error';
  }

  function escaparHtml(texto) {
    return String(texto || '').replace(/[&<>"']/g, function (caracter) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[caracter];
    });
  }

  function portadaLibro(libro) {
    if (libro.portadaUrl) {
      return '<div class="portada-libro"><img src="' + escaparHtml(libro.portadaUrl) + '" alt="Portada de ' + escaparHtml(libro.titulo) + '"></div>';
    }
    return '<div class="portada-libro">' + libro.titulo.substring(0, 2).toUpperCase() + '</div>';
  }

  // Pinta la tabla cada vez que se carga o filtra la lista.
  function renderizarTabla(lista) {
    let tbody = document.getElementById('tabla-libros');
    tbody.innerHTML = '';
    lista.forEach(function (libro) {
      let tr = document.createElement('tr');
      let estadoCalculado = disponibilidad(libro);
      tr.innerHTML =
        '<td>' + portadaLibro(libro) + '</td>' +
        '<td><div class="libro-info"><span class="libro-titulo">' + libro.titulo + '</span><span class="libro-autor">' + libro.autor + '</span></div></td>' +
        '<td><span class="isbn-texto">' + libro.isbn + '</span></td>' +
        '<td>' + libro.categoria + '</td>' +
        '<td><span class="badge ' + claseBadge(estadoCalculado) + '">' + libro.disponibles + ' de ' + libro.ejemplares + ' disponibles</span></td>' +
        '<td class="acciones-celda">' +
        '<button class="btn-icono" title="Ver detalle" data-accion="ver" data-id="' + libro.id + '"><i class="bi bi-eye"></i></button>' +
        '<button class="btn-icono" title="Editar" data-accion="editar" data-id="' + libro.id + '"><i class="bi bi-pencil"></i></button>' +
        '<button class="btn-icono" title="Eliminar" data-accion="eliminar" data-id="' + libro.id + '"><i class="bi bi-trash"></i></button>' +
        '</td>';
      tbody.appendChild(tr);
    });
    document.getElementById('info-paginacion-libros').textContent = 'Mostrando 1 a ' + lista.length + ' de ' + libros.length + ' libros';
  }

  // Aplica la búsqueda y los filtros seleccionados.
  function filtrar() {
    let busqueda = document.getElementById('buscar-libro').value.toLowerCase();
    let genero = document.getElementById('filtro-genero').value;
    let editorial = document.getElementById('filtro-editorial').value;
    let estado = document.getElementById('filtro-estado').value;
    let resultado = libros.filter(function (l) {
      return (l.titulo.toLowerCase().indexOf(busqueda) !== -1 || l.autor.toLowerCase().indexOf(busqueda) !== -1 || l.isbn.indexOf(busqueda) !== -1) &&
        (!genero || l.categoria === genero) && (!editorial || l.editorial === editorial) && (!estado || disponibilidad(l) === estado);
    });
    renderizarTabla(resultado);
  }

  // Dibuja los botones del paginador del listado de libros.
  function renderizarPaginacion() {
    let contenedor = document.getElementById('paginacion-libros');
    ['<', '1', '>'].forEach(function (p, i) {
      let btn = document.createElement('button');
      btn.textContent = p;
      if (i === 1) btn.className = 'activo';
      contenedor.appendChild(btn);
    });
  }

  //Abre el formulario limpio para registrar un libro nuevo.
  window.abrirModalLibro = function () {
    idEdicion = '';
    document.getElementById('modal-libro-titulo').textContent = 'Registrar Nuevo Libro';
    document.getElementById('form-libro').reset();
    document.getElementById('grupo-ejemplares-iniciales').classList.remove('oculto');
    document.getElementById('modal-libro').classList.add('visible');
  };

  //Reutiliza el mismo formulario, pero cargando los datos del libro elegido
  //Devuelve el libro del catálogo que coincide con el identificador
  function buscarLibro(id) {
    return libros.find(function (libro) { return libro.id === id; });
  }

  //Carga los datos del libro en el modal de edición. Los ejemplares no se
  //tocan aquí: cada copia tiene su propio ciclo de vida en Inventario.
  function editarLibro(id) {
    idEdicion = id;
    let libro = buscarLibro(id);
    if (!libro) return;
    document.getElementById('modal-libro-titulo').textContent = 'Editar Libro';
    document.getElementById('libro-titulo').value = libro.titulo;
    document.getElementById('libro-autor').value = libro.autor;
    document.getElementById('libro-isbn').value = libro.isbn;
    document.getElementById('libro-editorial').value = libro.editorial;
    document.getElementById('libro-genero').value = libro.categoria;
    document.getElementById('libro-anio').value = libro.anio;
    document.getElementById('libro-portada').value = libro.portadaUrl || '';
    document.getElementById('grupo-ejemplares-iniciales').classList.add('oculto');
    document.getElementById('modal-libro').classList.add('visible');
  }

  //Muestra los datos del libro en modo lectura.
  function verDetalleLibro(id) {
    let libro = buscarLibro(id);
    if (!libro) return;
    let contenido = document.getElementById('detalle-libro-contenido');
    contenido.innerHTML =
      '<div class="detalle-grid">' +
      '<div class="detalle-campo"><div class="detalle-label">Portada</div><div class="detalle-valor">' + portadaLibro(libro) + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Título</div><div class="detalle-valor">' + libro.titulo + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Autor</div><div class="detalle-valor">' + libro.autor + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">ISBN</div><div class="detalle-valor">' + libro.isbn + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Editorial</div><div class="detalle-valor">' + libro.editorial + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Género</div><div class="detalle-valor">' + libro.categoria + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Año</div><div class="detalle-valor">' + libro.anio + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">URL de Portada</div><div class="detalle-valor">' + (libro.portadaUrl ? escaparHtml(libro.portadaUrl) : 'Sin portada') + '</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Ejemplares</div><div class="detalle-valor">' + libro.disponibles + ' de ' + libro.ejemplares + ' disponibles</div></div>' +
      '<div class="detalle-campo"><div class="detalle-label">Disponibilidad</div><div class="detalle-valor"><span class="badge ' + claseBadge(disponibilidad(libro)) + '">' + disponibilidad(libro) + '</span></div></div>' +
      '</div>';
    document.getElementById('modal-detalle-libro').classList.add('visible');
  }

  //Pide confirmación antes de eliminar un libro del catálogo.
  function pedirConfirmacionEliminar(id) {
    let libro = buscarLibro(id);
    if (!libro) return;
    idEliminar = id;
    document.getElementById('texto-eliminar-libro').textContent = 'Confirma si deseas eliminar "' + libro.titulo + '" del catálogo.';
    document.getElementById('modal-eliminar-libro').classList.add('visible');
  }

  //Elimina el libro confirmado y refresca el listado.
  function eliminarLibroConfirmado() {
    if (!idEliminar) return;
    libros = libros.filter(function (libro) { return libro.id !== idEliminar; });
    idEliminar = '';
    cerrarModal('modal-eliminar-libro');
    filtrar();
  }

  //Guarda un libro nuevo o actualiza los datos bibliográficos de uno
  //existente. Editar un libro nunca cambia su cantidad de ejemplares: eso
  //se gestiona ejemplar por ejemplar desde Inventario.
  window.guardarLibro = function () {
    let datos = {
      titulo: document.getElementById('libro-titulo').value,
      autor: document.getElementById('libro-autor').value,
      isbn: document.getElementById('libro-isbn').value,
      editorial: document.getElementById('libro-editorial').value,
      categoria: document.getElementById('libro-genero').value,
      anio: parseInt(document.getElementById('libro-anio').value) || 2026,
      portadaUrl: document.getElementById('libro-portada').value.trim()
    };
    if (!datos.titulo || !datos.autor) return;

    if (idEdicion) {
      let libroActual = buscarLibro(idEdicion);
      if (!libroActual) return;
      datos.id = idEdicion;
      datos.ejemplares = libroActual.ejemplares;
      datos.disponibles = libroActual.disponibles;
      libros[libros.indexOf(libroActual)] = datos;
    } else {
      datos.id = 'LIB-DEMO-' + String(libros.length + 1).padStart(3, '0');
      datos.ejemplares = parseInt(document.getElementById('libro-ejemplares').value) || 1;
      datos.disponibles = datos.ejemplares;
      libros.push(datos);
    }
    cerrarModal('modal-libro');
    filtrar();
  };

  //Cierra el modal indicado desde los botones con data-cerrar-modal
  window.cerrarModal = function (id) {
    document.getElementById(id).classList.remove('visible');
  };

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      window.cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  document.getElementById('btn-guardar-libro').addEventListener('click', window.guardarLibro);
  document.getElementById('btn-confirmar-eliminar-libro').addEventListener('click', eliminarLibroConfirmado);

  //Despacha los botones de ver, editar y eliminar de cada fila
  document.getElementById('tabla-libros').addEventListener('click', function (e) {
    let boton = e.target.closest('[data-accion]');
    if (!boton) return;
    let id = boton.getAttribute('data-id');
    let accion = boton.getAttribute('data-accion');
    if (accion === 'ver') verDetalleLibro(id);
    if (accion === 'editar') editarLibro(id);
    if (accion === 'eliminar') pedirConfirmacionEliminar(id);
  });

  //Conecta el botón de la cabecera para registrar un libro nuevo
  document.querySelector('.pagina-acciones .btn').addEventListener('click', window.abrirModalLibro);

  document.getElementById('buscar-libro').addEventListener('input', filtrar);
  document.getElementById('filtro-genero').addEventListener('change', filtrar);
  document.getElementById('filtro-editorial').addEventListener('change', filtrar);
  document.getElementById('filtro-estado').addEventListener('change', filtrar);

  renderizarStats();
  cargarFiltros();
  renderizarTabla(libros);
  renderizarPaginacion();

  //Abre el modal de registro si la URL lleva el ancla correspondiente
  if (window.location.hash === '#registrar-libro') {
    window.abrirModalLibro();
  }

})();
