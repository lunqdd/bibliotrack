//Panel del lector: préstamos activos, estado y recomendaciones

(function () {
  'use strict';

  //Indicadores de cabecera del lector, todos derivables de prestamos/devoluciones
  let statsLector = [
    { icono: 'bi-book', label: 'Libros en Casa', valor: '2', badge: 'Actual', badgeTipo: 'info' },
    { icono: 'bi-calendar-event', label: 'Próxima Entrega', valor: '4 Días' },
    { icono: 'bi-journal-text', label: 'Préstamos Totales', valor: '3' },
    { icono: 'bi-journal-check', label: 'Devoluciones Este Año', valor: '1', badge: '2026', badgeTipo: 'info', tipo: 'destacado' }
  ];

  //Préstamos vigentes que se muestran en la tabla de actividad
  let prestamosLector = [
    { libro: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', vencimiento: '08 Jul, 2026', estado: 'En tiempo', progreso: 50, claseProgreso: 'barra-exito' },
    { libro: 'El Principito', autor: 'Antoine de Saint-Exupéry', vencimiento: '04 Jul, 2026', estado: 'Vence pronto', progreso: 72, claseProgreso: 'barra-error' }
  ];

  //Otros títulos del catálogo, sin algoritmo de recomendación detrás
  let recomendados = [
    { titulo: 'Sapiens', autor: 'Yuval Noah Harari' }
  ];

  //Prepara el modal de solicitud de préstamo y lo muestra
  function abrirModalPrestamo() {
    document.getElementById('form-prestamo').reset();
    limpiarErroresPrestamo();
    document.getElementById('modal-prestamo').classList.add('visible');
  }

  //Cierra el modal indicado.
  function cerrarModal(id) {
    document.getElementById(id).classList.remove('visible');
  }

  //Restablece el estado visual de los campos y oculta los mensajes de error
  function limpiarErroresPrestamo() {
    document.getElementById('prestamo-libro').classList.remove('campo-invalido');
    document.getElementById('prestamo-fecha').classList.remove('campo-invalido');
    document.getElementById('error-prestamo-libro').style.display = 'none';
    document.getElementById('error-prestamo-fecha').style.display = 'none';
  }

  //Marca un campo como inválido y muestra el mensaje asociado
  function mostrarErrorPrestamo(campoId, errorId, mensaje) {
    document.getElementById(campoId).classList.add('campo-invalido');
    let error = document.getElementById(errorId);
    error.textContent = mensaje;
    error.style.display = 'block';
  }

  //Valida los campos del modal y muestra el resultado al lector
  function guardarPrestamo() {
    limpiarErroresPrestamo();

    let libro = document.getElementById('prestamo-libro').value;
    let fecha = document.getElementById('prestamo-fecha').value;
    let mensajeLector = document.getElementById('mensaje-lector');
    let valido = true;

    if (!libro) {
      mostrarErrorPrestamo('prestamo-libro', 'error-prestamo-libro', 'Selecciona un libro para solicitar.');
      valido = false;
    }

    if (!fecha) {
      mostrarErrorPrestamo('prestamo-fecha', 'error-prestamo-fecha', 'Selecciona la fecha de retiro.');
      valido = false;
    }

    if (!valido) return;

    mensajeLector.textContent = 'Solicitud registrada para "' + libro + '". Queda pendiente de aprobación.';
    mensajeLector.style.display = 'block';
    cerrarModal('modal-prestamo');
  }

  //Muestra la fecha actual y el saludo del lector
  function renderizarFecha() {
    let ahora = new Date();
    let dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    let meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    document.getElementById('fecha-lector').textContent = dias[ahora.getDay()] + ', ' + ahora.getDate() + ' de ' + meses[ahora.getMonth()] + ' ' + ahora.getFullYear();
    document.getElementById('saludo-lector').textContent = 'Bienvenido de nuevo, Derek';
  }

  //Construye las tarjetas de resumen del lector
  function renderizarStats() {
    let contenedor = document.getElementById('stats-lector');
    statsLector.forEach(function (s) {
      let card = document.createElement('div');
      card.className = 'stat-card' + (s.tipo === 'destacado' ? ' stat-destacado' : '');
      card.innerHTML =
        '<div class="stat-header"><i class="bi ' + s.icono + ' stat-icono"></i>' +
        (s.badge ? '<span class="badge badge-' + s.badgeTipo + '">' + s.badge + '</span>' : '') + '</div>' +
        '<p class="stat-label">' + s.label + '</p><p class="stat-valor">' + s.valor + '</p>';
      contenedor.appendChild(card);
    });
  }

  //Pinta la tabla con los préstamos activos y su barra de progreso
  function renderizarActividad() {
    let tbody = document.getElementById('tabla-actividad-lector');
    prestamosLector.forEach(function (p) {
      let tr = document.createElement('tr');
      let estadoClase = p.estado === 'En tiempo' ? 'badge-exito' : 'badge-error';
      tr.innerHTML =
        '<td><div><strong>' + p.libro + '</strong><br><span class="isbn-texto">' + p.autor + '</span></div></td>' +
        '<td>' + p.vencimiento + '<div class="barra-progreso-prestamo"><div class="barra-progreso-relleno ' + p.claseProgreso + '" style="width:' + p.progreso + '%"></div></div></td>' +
        '<td><span class="badge ' + estadoClase + '">' + p.estado + '</span></td>';
      tbody.appendChild(tr);
    });
  }

  //Lista los libros sugeridos para el lector
  function renderizarRecomendados() {
    let contenedor = document.getElementById('recomendados');
    recomendados.forEach(function (libro) {
      let item = document.createElement('div');
      item.className = 'recomendado-item';
      item.innerHTML =
        '<div class="recomendado-portada">' + libro.titulo.substring(0, 2).toUpperCase() + '</div>' +
        '<div class="recomendado-info">' +
          '<h4>' + libro.titulo + '</h4>' +
          '<p>' + libro.autor + '</p>' +
          '<p><i class="bi bi-star-fill estrella-mini"></i></p>' +
        '</div>';
      contenedor.appendChild(item);
    });
  }

  //Conecta los botones de solicitud de préstamo
  document.getElementById('btn-nuevo-prestamo').addEventListener('click', abrirModalPrestamo);
  document.getElementById('btn-guardar-prestamo').addEventListener('click', guardarPrestamo);

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  //Cierra el modal si se hace clic fuera del contenido.
  document.getElementById('modal-prestamo').addEventListener('click', function (evento) {
    if (evento.target.id === 'modal-prestamo') {
      cerrarModal('modal-prestamo');
    }
  });

  renderizarFecha();
  renderizarStats();
  renderizarActividad();
  renderizarRecomendados();

})();
