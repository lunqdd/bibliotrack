//Reportes: estadísticas y gráficos sencillos del movimiento de libros

(function () {
  'use strict';

  //Indicadores globales de la sección de reportes
  let statsReportes = [
    { icono: 'bi-book', label: 'Total Préstamos', valor: '7', trend: 'Demo' },
    { icono: 'bi-graph-up', label: 'Tasa de Retorno', valor: '14%' },
    { icono: 'bi-people', label: 'Usuarios Registrados', valor: '4', trend: 'activos' },
    { icono: 'bi-collection', label: 'Ejemplares en Circulación', valor: '6' }
  ];

  //Movimientos mensuales de préstamos y devoluciones
  let flujoMensual = [
    { mes: 'Ene', prestados: 0, devueltos: 0 },
    { mes: 'Feb', prestados: 0, devueltos: 0 },
    { mes: 'Mar', prestados: 0, devueltos: 0 },
    { mes: 'Abr', prestados: 0, devueltos: 0 },
    { mes: 'May', prestados: 0, devueltos: 0 },
    { mes: 'Jun', prestados: 7, devueltos: 1 }
  ];

  //Títulos con mayor número de préstamos en el periodo
  let librosMasPrestados = [
    { nombre: 'Breve Historia del Tiempo', prestamos: 3 },
    { nombre: 'Cien Años de Soledad', prestamos: 1 },
    { nombre: 'Historia de Roma', prestamos: 1 },
    { nombre: 'Don Quijote de la Mancha', prestamos: 1 }
  ];

  //Usuarios con mayor cantidad de préstamos registrados
  let usuariosMasActivos = [
    { nombre: 'Derek Jensen', prestamos: 3 },
    { nombre: 'Abby Chavarría', prestamos: 3 },
    { nombre: 'María Peña', prestamos: 1 },
    { nombre: 'Luna Delgado', prestamos: 0 }
  ];

  //Cantidad de secciones del inventario en cada estado
  let estadoInventario = [
    { estado: 'Completo', cantidad: 4, clase: 'badge-exito' },
    { estado: 'Parcial', cantidad: 3, clase: 'badge-alerta' },
    { estado: 'Agotado', cantidad: 1, clase: 'badge-error' }
  ];

  //Construye las tarjetas de estadísticas del reporte
  function renderizarStats() {
    let contenedor = document.getElementById('stats-reportes');
    statsReportes.forEach(function (s) {
      let card = document.createElement('div');
      card.className = 'stat-card';
      card.innerHTML =
        '<div class="stat-header"><i class="bi ' + s.icono + ' stat-icono"></i>' +
        (s.trend ? '<span class="stat-trend">' + s.trend + '</span>' : '') + '</div>' +
        '<p class="stat-label">' + s.label + '</p><p class="stat-valor">' + s.valor + '</p>';
      contenedor.appendChild(card);
    });
  }

  //Dibuja el gráfico de barras con préstamos y devoluciones por mes
  function renderizarGraficoFlujo() {
    let contenedor = document.getElementById('grafico-flujo');
    flujoMensual.forEach(function (mes) {
      let grupo = document.createElement('div');
      grupo.className = 'barra-doble-grupo';
      let par = document.createElement('div');
      par.className = 'barra-doble-par';

      let b1 = document.createElement('div');
      b1.className = 'barra-doble';
      b1.style.height = (mes.prestados === 0 ? 0 : Math.max(8, Math.round((mes.prestados / 7) * 155))) + 'px';
      b1.style.backgroundColor = 'var(--primary)';

      let b2 = document.createElement('div');
      b2.className = 'barra-doble';
      b2.style.height = (mes.devueltos === 0 ? 0 : Math.max(8, Math.round((mes.devueltos / 7) * 155))) + 'px';
      b2.style.backgroundColor = 'var(--surface-tint)';

      par.appendChild(b1);
      par.appendChild(b2);

      let label = document.createElement('span');
      label.className = 'barra-doble-label';
      label.textContent = mes.mes;

      grupo.appendChild(par);
      grupo.appendChild(label);
      contenedor.appendChild(grupo);
    });
  }

  //Pinta una lista de nombre + barra proporcional al máximo de préstamos
  function renderizarLista(contenedorId, lista) {
    let contenedor = document.getElementById(contenedorId);
    let maximo = Math.max.apply(null, lista.map(function (l) { return l.prestamos; }));
    lista.forEach(function (l) {
      let porcentaje = Math.round((l.prestamos / maximo) * 100);
      let item = document.createElement('div');
      item.className = 'categoria-item';
      item.innerHTML =
        '<span class="categoria-nombre">' + l.nombre + '</span>' +
        '<div class="categoria-barra"><div class="categoria-barra-relleno" style="width:' + porcentaje + '%"></div></div>' +
        '<span class="categoria-porcentaje">' + l.prestamos + '</span>';
      contenedor.appendChild(item);
    });
  }

  //Lista el conteo de secciones de inventario por estado
  function renderizarEstadoInventario() {
    let contenedor = document.getElementById('estado-inventario');
    estadoInventario.forEach(function (e) {
      let item = document.createElement('div');
      item.className = 'reporte-item';
      item.innerHTML =
        '<div class="reporte-info"><strong>' + e.estado + '</strong></div>' +
        '<span class="badge ' + e.clase + '">' + e.cantidad + ' secciones</span>';
      contenedor.appendChild(item);
    });
  }

  renderizarStats();
  renderizarGraficoFlujo();
  renderizarLista('libros-mas-prestados', librosMasPrestados);
  renderizarLista('usuarios-mas-activos', usuariosMasActivos);
  renderizarEstadoInventario();

})();
