//Portada: carga los totales reales del sistema por AJAX al abrir la página.

$(function () {

  $.get('index.php?controller=inicio&action=statsInicio', function (data) {
    $('#dato-libros').text(data.libros);
    $('#dato-prestamos').text(data.prestamos);
    $('#dato-usuarios').text(data.usuarios);
  }, 'json');

});
