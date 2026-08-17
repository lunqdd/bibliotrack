//Inventario: filtro sobre la tabla renderizada por el servidor y edición por AJAX

$(function () {

  let form = $('#form-inventario');
  let error = $('#mensaje-error-inventario');

  //Filtra las filas ya renderizadas por ubicación/libro y por estado de sección
  function filtrar() {
    let busqueda = $('#buscar-inventario').val().toLowerCase();
    let estado = $('#filtro-estado-inv').val();

    $('#tabla-inventario tr').each(function () {
      let fila = $(this);
      let coincide = fila.data('texto').indexOf(busqueda) !== -1 && (!estado || fila.data('estado') === estado);
      fila.toggle(coincide);
    });
  }

  //Carga los datos de la sección elegida en el modal de edición
  function abrirEdicion(boton) {
    error.hide().text('');
    $('#inventario-libro-id').val(boton.data('libro-id'));
    $('#inventario-ubicacion-id').val(boton.data('ubicacion-id'));
    $('#inventario-pasillo').val(boton.data('pasillo'));
    $('#inventario-estante').val(boton.data('estante'));
    $('#inventario-estanteria').val(boton.data('en-estante'));
    $('#modal-inventario').addClass('visible');
  }

  $('#tabla-inventario').on('click', '[data-accion="editar"]', function () {
    abrirEdicion($(this));
  });

  $('[data-cerrar-modal]').on('click', function () {
    $('#' + $(this).data('cerrar-modal')).removeClass('visible');
  });

  //Guarda la sección y refresca para reflejar los ejemplares recalculados
  form.on('submit', function (evento) {
    evento.preventDefault();
    error.hide().text('');

    $.post('index.php?controller=inventario&action=actualizarInventario', form.serialize(), function (data) {
      if (data.response !== '00') {
        error.text(data.message).show();
        return;
      }
      window.location.reload();
    }, 'json');
  });

  $('#buscar-inventario').on('input', filtrar);
  $('#filtro-estado-inv').on('change', filtrar);

});
