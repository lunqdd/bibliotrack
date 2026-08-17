//menu.js
//Funcionalidades compartidas de navegación:
// - Menú hamburguesa en vistas móviles.
// - Dropdown del usuario en la barra superior.
// - Cierre de sesión por AJAX.
//Se vincula en todas las páginas que usan sidebar + topbar.

$(function () {

  //Cierra la sesión por AJAX y redirige al inicio.
  $('#btn-logout').on('click', function (evento) {
    evento.preventDefault();
    $.post('index.php?controller=login&action=logout', function () {
      window.location = 'index.php';
    });
  });

  //Controla el dropdown del perfil de usuario en el topbar.
  let usuario = $('.topbar-usuario');
  let menuUsuario = usuario.find('.menu-usuario');

  if (usuario.length && menuUsuario.length) {
    usuario.on('click', function (evento) {
      evento.stopPropagation();
      menuUsuario.toggleClass('visible');
    });

    $(document).on('click', function () {
      menuUsuario.removeClass('visible');
    });

    $(document).on('keydown', function (evento) {
      if (evento.key === 'Escape') menuUsuario.removeClass('visible');
    });
  }

  //Controla el menú hamburguesa en vistas móviles.
  //En escritorio el sidebar es fijo, por lo que el botón no se muestra.
  let boton = $('.btn-menu-hamburguesa');
  let sidebar = $('.sidebar');
  let overlay = $('.sidebar-overlay');

  if (boton.length && sidebar.length) {

    function abrir() {
      sidebar.addClass('abierto');
      overlay.addClass('visible');
      boton.attr('aria-expanded', 'true');
    }

    function cerrar() {
      sidebar.removeClass('abierto');
      overlay.removeClass('visible');
      boton.attr('aria-expanded', 'false');
    }

    boton.on('click', function () {
      if (sidebar.hasClass('abierto')) {
        cerrar();
      } else {
        abrir();
      }
    });

    overlay.on('click', cerrar);

    $(document).on('keydown', function (evento) {
      if (evento.key === 'Escape') cerrar();
    });

    //Si se redimensiona a escritorio, cerrar el menú móvil.
    $(window).on('resize', function () {
      if ($(window).width() > 768) cerrar();
    });
  }

});
