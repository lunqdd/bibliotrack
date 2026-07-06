// Catálogo del lector: búsqueda, filtro y solicitud de préstamo

(function () {
  'use strict';

  // Datos de muestra del catálogo disponible para el lector
  let catalogo = [
    { id: 'LIB-001', titulo: 'Cien años de soledad', autor: 'Gabriel García Márquez', categoria: 'Novela', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780307350433-M.jpg', disponible: true, ejemplares: 5 },
    { id: 'LIB-002', titulo: 'Breve historia del tiempo', autor: 'Stephen Hawking', categoria: 'Ciencia', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780553109580-M.jpg', disponible: false, ejemplares: 0 },
    { id: 'LIB-007', titulo: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', categoria: 'Novela', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9788420412146-M.jpg', disponible: true, ejemplares: 5 },
    { id: 'LIB-008', titulo: 'Sapiens', autor: 'Yuval Noah Harari', categoria: 'Historia', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780062316097-M.jpg', disponible: true, ejemplares: 4 },
    { id: 'LIB-005', titulo: 'El Principito', autor: 'Antoine de Saint-Exupéry', categoria: 'Novela', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780156012195-M.jpg', disponible: true, ejemplares: 7 },
    { id: 'LIB-006', titulo: 'Cosmos', autor: 'Carl Sagan', categoria: 'Ciencia', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780345539434-M.jpg', disponible: false, ejemplares: 0 },
    { id: 'LIB-003', titulo: 'Historia de Roma', autor: 'Indro Montanelli', categoria: 'Historia', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9788497593151-M.jpg', disponible: true, ejemplares: 2 },
    { id: 'LIB-004', titulo: 'El arte del diseño', autor: 'Alice Rawsthorn', categoria: 'Arte', portadaUrl: 'https://covers.openlibrary.org/b/isbn/9780141984254-M.jpg', disponible: true, ejemplares: 4 }
  ];
  //Identificador del libro en curso dentro del modal de confirmación
  let idCatalogo = '';

  //Puebla el selector de género con las categorías presentes en el catálogo
  function cargarFiltros() {
    let generos = [];
    catalogo.forEach(function (l) { if (generos.indexOf(l.categoria) === -1) generos.push(l.categoria); });
    let select = document.getElementById('filtro-cat-genero');
    generos.forEach(function (g) { let o = document.createElement('option'); o.value = g; o.textContent = g; select.appendChild(o); });
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

  //Pinta la tabla con la lista de libros recibida
  function renderizarTabla(lista) {
    let tbody = document.getElementById('tabla-catalogo');
    tbody.innerHTML = '';
    lista.forEach(function (libro) {
      let tr = document.createElement('tr');
      let boton = libro.disponible
        ? '<button class="btn btn-sm btn-primario" data-id="' + libro.id + '">Solicitar</button>'
        : '<button class="btn btn-sm btn-outline" disabled>No disponible</button>';
      tr.innerHTML =
        '<td>' + portadaLibro(libro) + '</td>' +
        '<td><div class="libro-info"><span class="libro-titulo">' + libro.titulo + '</span><span class="libro-autor">' + libro.autor + '</span></div></td>' +
        '<td>' + libro.categoria + '</td>' +
        '<td><span class="badge ' + (libro.disponible ? 'badge-exito' : 'badge-error') + '">' + (libro.disponible ? libro.ejemplares + ' disponibles' : 'No disponible') + '</span></td>' +
        '<td>' + boton + '</td>';
      tbody.appendChild(tr);
    });
  }

  //Muestra un mensaje informativo en el área de notificaciones
  function mostrarMensaje(texto) {
    let mensaje = document.getElementById('mensaje-catalogo');
    mensaje.textContent = texto;
    mensaje.style.display = 'block';
  }

  //Devuelve el libro del catálogo que coincide con el identificador
  function buscarLibro(id) {
    return catalogo.find(function (libro) { return libro.id === id; });
  }

  //Abre el modal de confirmación de la solicitud de préstamo
  function abrirConfirmacion(id) {
    let libro = buscarLibro(id);
    if (!libro || !libro.disponible) return;
    idCatalogo = id;
    document.getElementById('modal-catalogo-titulo').textContent = 'Confirmar Solicitud';
    document.getElementById('texto-catalogo').textContent = 'Confirma si deseas solicitar "' + libro.titulo + '".';
    document.getElementById('btn-confirmar-catalogo').textContent = 'Solicitar';
    document.getElementById('modal-catalogo').classList.add('visible');
  }

  //Procesa la confirmación y muestra el mensaje resultante al lector
  function confirmarAccion() {
    if (!idCatalogo) return;
    let libro = buscarLibro(idCatalogo);
    if (!libro) return;
    let texto = 'Solicitud registrada para "' + libro.titulo + '". Queda pendiente de aprobación.';
    idCatalogo = '';
    cerrarModal('modal-catalogo');
    mostrarMensaje(texto);
  }

  //Cierra el modal indicado ocultando la superposición
  function cerrarModal(id) {
    document.getElementById(id).classList.remove('visible');
  }

  //Filtra el catálogo según el texto de búsqueda y la categoría seleccionada
  function filtrar() {
    let busqueda = document.getElementById('buscar-catalogo').value.toLowerCase();
    let genero = document.getElementById('filtro-cat-genero').value;
    let resultado = catalogo.filter(function (l) {
      return (l.titulo.toLowerCase().indexOf(busqueda) !== -1 || l.autor.toLowerCase().indexOf(busqueda) !== -1) &&
             (!genero || l.categoria === genero);
    });
    renderizarTabla(resultado);
  }

  /* Conecta los controles de búsqueda, filtro y acciones de la tabla. */
  document.getElementById('buscar-catalogo').addEventListener('input', filtrar);
  document.getElementById('filtro-cat-genero').addEventListener('change', filtrar);
  document.getElementById('tabla-catalogo').addEventListener('click', function (evento) {
    let boton = evento.target.closest('[data-id]');
    if (!boton) return;
    abrirConfirmacion(boton.getAttribute('data-id'));
  });

  document.getElementById('btn-confirmar-catalogo').addEventListener('click', confirmarAccion);
  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  cargarFiltros();
  renderizarTabla(catalogo);

})();
