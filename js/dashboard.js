//Dashboard: refresca la tabla de actividad reciente por AJAX sin recargar la página.

$(function () {

  let tabla = $('#tabla-actividad');
  let boton = $('#btn-actualizar-actividad');
  let badgesAccion = { 'Préstamo': 'alerta', 'Devolución': 'exito' };

  boton.on('click', function () {
    $.get('index.php?controller=dashboard&action=actividadReciente', function (data) {
      let lista = data.actividad || [];

      if (!lista.length) {
        tabla.html('<tr><td colspan="4">Sin movimientos recientes.</td></tr>');
        return;
      }

      tabla.empty();
      lista.forEach(function (mov) {
        let badge = badgesAccion[mov.accion] || 'info';
        tabla.append(
          '<tr>'
          + '<td>' + mov.usuario + '</td>'
          + '<td><em>' + mov.libro + '</em></td>'
          + '<td><span class="badge badge-' + badge + '">' + mov.accion + '</span></td>'
          + '<td>' + mov.fecha + '</td>'
          + '</tr>'
        );
      });
    }, 'json');
  });

});
