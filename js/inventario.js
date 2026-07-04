//Inventario: existencias por ubicación y estado de cada sección

(function () {
  'use strict';

  //Registros de prueba del inventario con su ubicación física. El estado de sección no se guarda: se calcula a partir de ejemplares totales vs en estante
  let inventario = [
    { id: 'INV-001', pasillo: 'A', estante: '4', libro: 'Cien Años de Soledad', autor: 'Gabriel García Márquez', isbn: '978-0307350433', totales: 5, estanteria: 5 },
    { id: 'INV-002', pasillo: 'B', estante: '2', libro: 'Breve Historia del Tiempo', autor: 'Stephen Hawking', isbn: '978-0553109580', totales: 3, estanteria: 0 },
    { id: 'INV-003', pasillo: 'C', estante: '1', libro: 'Historia de Roma', autor: 'Indro Montanelli', isbn: '978-8497593151', totales: 2, estanteria: 1 },
    { id: 'INV-004', pasillo: 'D', estante: '2', libro: 'El arte del diseño', autor: 'Alice Rawsthorn', isbn: '978-0141984254', totales: 4, estanteria: 4 },
    { id: 'INV-005', pasillo: 'A', estante: '4', libro: 'El Principito', autor: 'Antoine de Saint-Exupéry', isbn: '978-0156012195', totales: 8, estanteria: 7 },
    { id: 'INV-006', pasillo: 'B', estante: '2', libro: 'Cosmos', autor: 'Carl Sagan', isbn: '978-0345539434', totales: 3, estanteria: 0 },
    { id: 'INV-007', pasillo: 'C', estante: '1', libro: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', isbn: '978-8420412146', totales: 6, estanteria: 5 },
    { id: 'INV-008', pasillo: 'D', estante: '2', libro: 'Sapiens', autor: 'Yuval Noah Harari', isbn: '978-0062316097', totales: 4, estanteria: 4 }
  ];

  //Calcula el estado de la sección a partir de los ejemplares en estante
  function calcularEstado(item) {
    if (item.estanteria <= 0) return 'Agotado';
    if (item.estanteria < item.totales) return 'Parcial';
    return 'Completo';
  }

  //Identificador del registro en curso dentro del modal de edición
  let idInventario = '';

  //Indicadores globales del inventario mostrados arriba
  let statsInventario = [
    { icono: 'bi-archive', label: 'Total Ejemplares', valor: '35' },
    { icono: 'bi-check-circle', label: 'Disponibles', valor: '26', barra: { porcentaje: 74, color: 'var(--exito)' } },
    { icono: 'bi-arrow-left-right', label: 'En Préstamo', valor: '6', barra: { porcentaje: 17, color: 'var(--alerta)' } },
    { icono: 'bi-exclamation-diamond', label: 'En Reparación', valor: '3', tipo: 'error' }
  ];

  //Construye las tarjetas de resumen del inventario
  function renderizarStats() {
    let contenedor = document.getElementById('stats-inventario');
    statsInventario.forEach(function (s) {
      let card = document.createElement('div');
      card.className = 'stat-card' + (s.tipo === 'error' ? ' stat-error' : '');
      let html = '<div class="stat-header"><i class="bi ' + s.icono + ' stat-icono"></i>';
      if (s.barra) html += '<span class="stat-trend">' + s.barra.porcentaje + '%</span>';
      html += '</div><p class="stat-label">' + s.label + '</p><p class="stat-valor">' + s.valor + '</p>';
      if (s.barra) html += '<div class="stat-barra"><div class="stat-barra-progreso" style="width:' + s.barra.porcentaje + '%;background-color:' + s.barra.color + '"></div></div>';
      if (s.sublabel) html += '<p class="stat-trend" style="color:var(--error)">' + s.sublabel + '</p>';
      card.innerHTML = html;
      contenedor.appendChild(card);
    });
  }

  //Devuelve la clase de badge según el estado del registro
  function claseEstado(estado) {
    if (estado === 'Completo') return 'badge-exito';
    if (estado === 'Parcial') return 'badge-alerta';
    return 'badge-error';
  }

  //Puebla el selector con los posibles estados de sección calculados
  function cargarFiltros() {
    let select = document.getElementById('filtro-estado-inv');
    ['Completo', 'Parcial', 'Agotado'].forEach(function (e) {
      let o = document.createElement('option'); o.value = e; o.textContent = e; select.appendChild(o);
    });
  }

  //Pinta la tabla con la lista de ubicaciones indicada
  function renderizarTabla(items) {
    let tbody = document.getElementById('tabla-inventario');
    tbody.innerHTML = '';
    items.forEach(function (item) {
      let estado = calcularEstado(item);
      let tr = document.createElement('tr');
      tr.innerHTML =
        '<td>Pasillo ' + item.pasillo + ' - Estante ' + item.estante + '</td>' +
        '<td><strong>' + item.libro + '</strong><br><span class="isbn-texto">' + item.autor + '</span></td>' +
        '<td><span class="isbn-texto">' + item.isbn + '</span></td>' +
        '<td class="texto-centro">' + item.totales + '</td>' +
        '<td class="texto-centro">' + item.estanteria + '</td>' +
        '<td><span class="badge ' + claseEstado(estado) + '"><i class="bi bi-circle-fill punto-estado"></i> ' + estado + '</span></td>' +
        '<td><button class="btn-icono" title="Editar" data-accion="editar" data-id="' + item.id + '"><i class="bi bi-pencil-square"></i></button></td>';
      tbody.appendChild(tr);
    });
  }

  //Devuelve el registro de inventario que coincide con el identificador
  function buscarInventario(id) {
    return inventario.find(function (item) { return item.id === id; });
  }

  //Carga los datos del registro en el modal de edición
  function abrirEdicionInventario(id) {
    let item = buscarInventario(id);
    if (!item) return;
    idInventario = id;
    document.getElementById('inventario-pasillo').value = item.pasillo;
    document.getElementById('inventario-estante').value = item.estante;
    document.getElementById('inventario-estanteria').value = item.estanteria;
    document.getElementById('modal-inventario').classList.add('visible');
  }

  //Actualiza el registro con los valores del formulario y refresca la tabla
  function guardarInventario() {
    if (!idInventario) return;
    let item = buscarInventario(idInventario);
    if (!item) return;
    let pasillo = document.getElementById('inventario-pasillo').value;
    let estante = document.getElementById('inventario-estante').value;
    let estanteria = parseInt(document.getElementById('inventario-estanteria').value);
    if (!pasillo || !estante || isNaN(estanteria)) return;

    item.pasillo = pasillo;
    item.estante = estante;
    item.estanteria = estanteria;
    idInventario = '';
    cerrarModal('modal-inventario');
    filtrar();
  }

  //Cierra el modal indicado ocultando la superposición
  function cerrarModal(id) {
    document.getElementById(id).classList.remove('visible');
  }

  //Filtra el inventario según la búsqueda y el estado seleccionados.
  function filtrar() {
    let busqueda = document.getElementById('buscar-inventario').value.toLowerCase();
    let estado = document.getElementById('filtro-estado-inv').value;
    let resultado = inventario.filter(function (item) {
      let ubicacionTexto = (item.pasillo + ' ' + item.estante).toLowerCase();
      return (ubicacionTexto.indexOf(busqueda) !== -1 || item.libro.toLowerCase().indexOf(busqueda) !== -1) &&
             (!estado || calcularEstado(item) === estado);
    });
    renderizarTabla(resultado);
  }

  //Genera los botones básicos del paginador
  function renderizarPaginacion() {
    let contenedor = document.getElementById('paginacion-inventario');
    ['<', '>'].forEach(function (p) { let btn = document.createElement('button'); btn.textContent = p; contenedor.appendChild(btn); });
  }

  //Conecta los controles de búsqueda, filtro, guardado y acciones de la tabla
  document.getElementById('buscar-inventario').addEventListener('input', filtrar);
  document.getElementById('filtro-estado-inv').addEventListener('change', filtrar);
  document.getElementById('btn-guardar-inventario').addEventListener('click', guardarInventario);
  document.getElementById('tabla-inventario').addEventListener('click', function (evento) {
    let boton = evento.target.closest('[data-accion="editar"]');
    if (!boton) return;
    abrirEdicionInventario(boton.getAttribute('data-id'));
  });

  document.querySelectorAll('[data-cerrar-modal]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      cerrarModal(boton.getAttribute('data-cerrar-modal'));
    });
  });

  renderizarStats();
  cargarFiltros();
  renderizarTabla(inventario);
  renderizarPaginacion();

})();
