// Inventario: existencias por ubicación filtradas y editadas contra el servidor por AJAX.

$(function () {

  let tabla = $('#tabla-inventario');
  let infoPaginacion = $('#info-paginacion-inv');
  let modal = $('#modal-inventario');
  let mensaje = $('#mensaje-error-inventario');
  let temporizadorBusqueda = null;

  //Devuelve la clase de badge según el estado de la sección
  function claseEstado(estado) {
    if (estado === 'Completo') return 'badge-exito';
    if (estado === 'Parcial') return 'badge-alerta';
    return 'badge-error';
  }

  function filaInventario(item) {
    return '<tr>'
      + '<td>Pasillo ' + item.pasillo + ' - Estante ' + item.estante + '</td>'
      + '<td><strong>' + item.titulo + '</strong><br><span class="isbn-texto">' + item.autor + '</span></td>'
      + '<td><span class="isbn-texto">' + item.isbn + '</span></td>'
      + '<td class="texto-centro">' + item.total + '</td>'
      + '<td class="texto-centro">' + item.en_estante + '</td>'
      + '<td><span class="badge ' + claseEstado(item.estado) + '"><i class="bi bi-circle-fill punto-estado"></i> ' + item.estado + '</span></td>'
      + '<td><button class="btn-icono" title="Editar" data-accion="editar" data-id="' + item.libro_id + '"><i class="bi bi-pencil-square"></i></button></td>'
      + '</tr>';
  }

  //Pide el inventario filtrado y repinta la tabla sin recargar la página.
  function buscarInventario() {
    $.get('index.php?controller=inventario&action=buscarInventario', {
      q: $('#buscar-inventario').val(),
      estado: $('#filtro-estado-inv').val(),
    }, function (data) {
      let lista = data.inventario || [];

      if (!lista.length) {
        tabla.html('<tr><td colspan="7">No se encontraron existencias con ese criterio.</td></tr>');
        infoPaginacion.text('0 registros encontrados');
        return;
      }

      tabla.empty();
      lista.forEach(function (item) { tabla.append(filaInventario(item)); });
      infoPaginacion.text(lista.length + (lista.length === 1 ? ' registro encontrado' : ' registros encontrados'));
    }, 'json');
  }

  $('#buscar-inventario').on('input', function () {
    clearTimeout(temporizadorBusqueda);
    temporizadorBusqueda = setTimeout(buscarInventario, 300);
  });
  $('#filtro-estado-inv').on('change', buscarInventario);

  //Trae los datos del registro y precarga el modal de edición
  function abrirEdicion(id) {
    mensaje.hide().text('');
    $.get('index.php?controller=inventario&action=edit', { id: id }, function (data) {
      if (data.response !== '00') return;
      let item = data.item;
      $('#inventario-libro-id').val(id);
      $('#inventario-pasillo').val(item.pasillo);
      $('#inventario-estante').val(item.estante);
      $('#inventario-estanteria').val(item.en_estante);
      modal.addClass('visible');
    }, 'json');
  }

  //Reubica el libro y ajusta el conteo de estantería por AJAX.
  $('#btn-guardar-inventario').on('click', function () {
    mensaje.hide().text('');

    let id = $('#inventario-libro-id').val();
    let pasillo = $('#inventario-pasillo').val().trim();
    let estante = $('#inventario-estante').val().trim();
    let enEstante = $('#inventario-estanteria').val();

    if (!id || !pasillo || !estante || enEstante === '') {
      mensaje.text('Completa el pasillo, el estante y la cantidad en estante.').show();
      return;
    }

    $.post('index.php?controller=inventario&action=update&id=' + id, {
      pasillo: pasillo,
      estante: estante,
      en_estante: enEstante,
    }, function (data) {
      if (data.response !== '00') {
        mensaje.text(data.message).show();
        return;
      }
      modal.removeClass('visible');
      buscarInventario();
    }, 'json');
  });

  tabla.on('click', '[data-accion="editar"]', function () {
    abrirEdicion($(this).data('id'));
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

});
