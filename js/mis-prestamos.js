//Préstamos del lector: pestaña de activos y pestaña de historial (solo consulta)

(function () {
  'use strict';

  //Préstamos vigentes del lector con su fecha de vencimiento
  let misPrestamos = [
    { titulo: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', fechaPrestamo: '24 Jun 2026', vencimiento: '08 Jul 2026', estado: 'En tiempo' },
    { titulo: 'El Principito', autor: 'Antoine de Saint-Exupéry', fechaPrestamo: '20 Jun 2026', vencimiento: '04 Jul 2026', estado: 'En tiempo' }
  ];

  //Préstamos concluidos del lector, con su estado final de devolución
  let historial = [
    { titulo: 'Cien Años de Soledad', autor: 'Gabriel García Márquez', fechaPrestamo: '12 Jun 2026', fechaDevolucion: '25 Jun 2026', estado: 'DEVUELTO' }
  ];

  //Llena la tabla con los préstamos activos del lector
  function renderizarTabla() {
    let tbody = document.getElementById('tabla-mis-prestamos');
    tbody.innerHTML = '';
    misPrestamos.forEach(function (p) {
      let tr = document.createElement('tr');
      let estadoClase = p.estado === 'En tiempo' ? 'badge-exito' : 'badge-error';
      tr.innerHTML =
        '<td><div class="libro-info"><span class="libro-titulo">' + p.titulo + '</span><span class="libro-autor">' + p.autor + '</span></div></td>' +
        '<td>' + p.fechaPrestamo + '</td>' +
        '<td>' + p.vencimiento + '</td>' +
        '<td><span class="badge ' + estadoClase + '">' + p.estado + '</span></td>';
      tbody.appendChild(tr);
    });
  }

  //Mapea el estado del historial a la clase de badge correspondiente
  function claseBadgeHistorial(estado) {
    if (estado === 'DEVUELTO') return 'badge-exito';
    return 'badge-error';
  }

  //Llena la tabla con el historial completo de préstamos del lector
  function renderizarHistorial() {
    let tbody = document.getElementById('tabla-historial');
    tbody.innerHTML = '';
    historial.forEach(function (h) {
      let tr = document.createElement('tr');
      tr.innerHTML =
        '<td><div class="libro-info"><span class="libro-titulo">' + h.titulo + '</span><span class="libro-autor">' + h.autor + '</span></div></td>' +
        '<td>' + h.fechaPrestamo + '</td>' +
        '<td>' + h.fechaDevolucion + '</td>' +
        '<td><span class="badge ' + claseBadgeHistorial(h.estado) + '">' + h.estado + '</span></td>';
      tbody.appendChild(tr);
    });
  }

  //Cambia entre la pestaña de préstamos activos y la de historial.
  function activarTab(nombre) {
    let activos = nombre === 'activos';
    document.getElementById('tab-activos').classList.toggle('activo', activos);
    document.getElementById('tab-historial').classList.toggle('activo', !activos);
    document.getElementById('tab-activos').setAttribute('aria-selected', String(activos));
    document.getElementById('tab-historial').setAttribute('aria-selected', String(!activos));
    document.getElementById('panel-activos').classList.toggle('oculto', !activos);
    document.getElementById('panel-historial').classList.toggle('oculto', activos);
  }

  renderizarTabla();
  renderizarHistorial();

  document.getElementById('tab-activos').addEventListener('click', function () { activarTab('activos'); });
  document.getElementById('tab-historial').addEventListener('click', function () { activarTab('historial'); });

})();
