//Préstamos del lector: cambio entre la pestaña de activos y la de historial.
//Las tablas se renderizan en PHP con los datos reales del usuario.

$(function () {

  function activarTab(nombre) {
    let activos = nombre === 'activos';
    $('#tab-activos').toggleClass('activo', activos).attr('aria-selected', String(activos));
    $('#tab-historial').toggleClass('activo', !activos).attr('aria-selected', String(!activos));
    $('#panel-activos').toggleClass('oculto', !activos);
    $('#panel-historial').toggleClass('oculto', activos);
  }

  $('#tab-activos').on('click', function () { activarTab('activos'); });
  $('#tab-historial').on('click', function () { activarTab('historial'); });

});
