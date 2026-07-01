//Panel principal: resumen, accesos rápidos y actividad reciente

(function () {
  'use strict';

  //Indicadores generales mostrados en las tarjetas superiores
  let estadisticas = [
    { icono: 'bi-book', label: 'Títulos Registrados', valor: '8', trend: '35 ejemplares' },
    { icono: 'bi-arrow-left-right', label: 'Préstamos Activos', valor: '6', badge: 'En circulación', badgeTipo: 'info' },
    { icono: 'bi-exclamation-triangle', label: 'Devoluciones Tardías', valor: '1', tipo: 'error' },
    { icono: 'bi-people', label: 'Usuarios Registrados', valor: '4', trend: 'Todos activos' }
  ];

  //Atajos a las pantallas de uso frecuente
  let acciones = [
    { icono: 'bi-book', texto: 'Registrar Libro', destino: 'libros.html#registrar-libro' },
    { icono: 'bi-arrow-left-right', texto: 'Nuevo Préstamo', destino: 'prestamos.html#nuevo-prestamo' },
    { icono: 'bi-person-plus', texto: 'Registrar Usuario', destino: 'usuarios.html#registrar-usuario' }
  ];

  //Movimientos más recientes del sistema (préstamos, devoluciones, atrasos)
  let actividades = [
    { usuario: 'Derek Jensen', libro: 'Sapiens', accion: 'Solicitud', tiempo: 'Hoy 09:15', tipoAccion: 'info' },
    { usuario: 'María Peña', libro: 'Historia de Roma', accion: 'Vencido', tiempo: '19 Jun 2026', tipoAccion: 'error' },
    { usuario: 'Derek Jensen', libro: 'Cien Años de Soledad', accion: 'Devolución', tiempo: '25 Jun 2026', tipoAccion: 'exito' },
    { usuario: 'Derek Jensen', libro: 'Don Quijote de la Mancha', accion: 'Préstamo', tiempo: '24 Jun 2026', tipoAccion: 'alerta' },
    { usuario: 'Abby Chavarría', libro: 'Breve Historia del Tiempo', accion: 'Préstamo', tiempo: '18 Jun 2026', tipoAccion: 'alerta' }
  ];

  //Valores por día empleados en el gráfico de barras de la semana
  let tendenciaSemanal = [
    { dia: 'LUN', valor: 1 },
    { dia: 'MAR', valor: 0 },
    { dia: 'MIÉ', valor: 2 },
    { dia: 'JUE', valor: 1 },
    { dia: 'VIE', valor: 0 },
    { dia: 'SÁB', valor: 1 },
    { dia: 'DOM', valor: 2 }
  ];

  //Títulos con mayor número de préstamos en el periodo
  let librosPopulares = [
    { titulo: 'Breve Historia del Tiempo', autor: 'Stephen Hawking', prestamos: 3 },
    { titulo: 'Cien Años de Soledad', autor: 'Gabriel García Márquez', prestamos: 1 },
    { titulo: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', prestamos: 1 }
  ];

  //Muestra la fecha actual y el saludo correspondiente a la hora
  function renderizarFecha() {
    let ahora = new Date();
    let dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    let meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    document.getElementById('fecha-actual').textContent = dias[ahora.getDay()] + ', ' + ahora.getDate() + ' de ' + meses[ahora.getMonth()] + ' ' + ahora.getFullYear();

    let hora = ahora.getHours();
    let saludo = hora < 12 ? 'Buenos días' : hora < 18 ? 'Buenas tardes' : 'Buenas noches';
    document.getElementById('saludo').textContent = saludo + ', Luna';
  }

  //Construye las tarjetas de estadísticas a partir del arreglo
  function renderizarStats() {
    let contenedor = document.getElementById('stats-container');
    estadisticas.forEach(function (stat) {
      let card = document.createElement('div');
      card.className = 'stat-card' + (stat.tipo === 'error' ? ' stat-error' : '');

      let html = '<div class="stat-header"><i class="bi ' + stat.icono + ' stat-icono"></i>';
      if (stat.trend) html += '<span class="stat-trend">' + stat.trend + '</span>';
      if (stat.badge) html += '<span class="badge badge-' + stat.badgeTipo + '">' + stat.badge + '</span>';
      html += '</div>';
      html += '<p class="stat-label">' + stat.label + '</p>';
      html += '<p class="stat-valor">' + stat.valor + '</p>';

      card.innerHTML = html;
      contenedor.appendChild(card);
    });
  }

  //Pinta los botones de acciones rápidas y su redirección
  function renderizarAcciones() {
    let contenedor = document.getElementById('acciones-rapidas');
    acciones.forEach(function (accion) {
      let btn = document.createElement('button');
      btn.className = 'accion-rapida';
      btn.type = 'button';
      btn.innerHTML = '<i class="bi ' + accion.icono + '"></i> ' + accion.texto;
      btn.addEventListener('click', function () {
        window.location.href = accion.destino;
      });
      contenedor.appendChild(btn);
    });
  }

  //Llena la tabla de actividad reciente con los últimos movimientos
  function renderizarActividad() {
    let tbody = document.getElementById('tabla-actividad');
    actividades.forEach(function (act) {
      let tr = document.createElement('tr');
      tr.innerHTML =
        '<td>' + act.usuario + '</td>' +
        '<td><em>' + act.libro + '</em></td>' +
        '<td><span class="badge badge-' + act.tipoAccion + '">' + act.accion + '</span></td>' +
        '<td>' + act.tiempo + '</td>';
      tbody.appendChild(tr);
    });
  }

  //Dibuja el gráfico de barras con el movimiento de la semana
  function renderizarGrafico() {
    let contenedor = document.getElementById('grafico-tendencia');
    let maxValor = Math.max.apply(null, tendenciaSemanal.map(function (d) { return d.valor; }));

    tendenciaSemanal.forEach(function (dia, i) {
      let grupo = document.createElement('div');
      grupo.className = 'barra-grupo';
      let altura = Math.round((dia.valor / maxValor) * 110);
      let esHoy = i === tendenciaSemanal.length - 1;

      let barra = document.createElement('div');
      barra.className = 'barra';
      barra.style.height = altura + 'px';
      barra.style.backgroundColor = esHoy ? 'var(--surface-tint)' : 'var(--primary)';
      grupo.appendChild(barra);

      if (esHoy) {
        let tag = document.createElement('span');
        tag.className = 'barra-tag';
        tag.textContent = 'Hoy';
        grupo.appendChild(tag);
      }

      let label = document.createElement('span');
      label.className = 'barra-label';
      label.textContent = dia.dia;
      grupo.appendChild(label);

      contenedor.appendChild(grupo);
    });
  }

  //Lista los libros más prestados con su conteo
  function renderizarLibrosPopulares() {
    let contenedor = document.getElementById('libros-populares');
    librosPopulares.forEach(function (libro) {
      let item = document.createElement('div');
      item.className = 'libro-popular';
      let iniciales = libro.titulo.substring(0, 2).toUpperCase();
      item.innerHTML =
        '<div class="libro-popular-portada">' + iniciales + '</div>' +
        '<div class="libro-popular-info">' +
          '<h4>' + libro.titulo + '</h4>' +
          '<p>' + libro.autor + '</p>' +
          '<span class="prestamos-count"><i class="bi bi-star-fill"></i> ' + libro.prestamos + ' préstamos</span>' +
        '</div>';
      contenedor.appendChild(item);
    });
  }

  renderizarFecha();
  renderizarStats();
  renderizarAcciones();
  renderizarActividad();
  renderizarGrafico();
  renderizarLibrosPopulares();

})();
