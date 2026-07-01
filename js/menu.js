//menu.js
//Funcionalidades compartidas de navegación:
// - Menú hamburguesa en vistas móviles.
//- Dropdown del usuario en la barra superior.
//Se vincula en todas las páginas que usan sidebar + topbar.
 
(function () {
  'use strict';

  //Controla el dropdown del perfil de usuario en el topbar.
  function inicializarMenuUsuario() {
    let usuario = document.querySelector('.topbar-usuario');
    if (!usuario) return;

    let menu = usuario.querySelector('.menu-usuario');
    if (!menu) return;

    usuario.addEventListener('click', function (evento) {
      evento.stopPropagation();
      menu.classList.toggle('visible');
    });

    document.addEventListener('click', function () {
      menu.classList.remove('visible');
    });

    document.addEventListener('keydown', function (evento) {
      if (evento.key === 'Escape') menu.classList.remove('visible');
    });
  }

   //Controla el menú hamburguesa en vistas móviles.
   //En escritorio el sidebar es fijo, por lo que el botón no se muestra.
  function inicializarMenuHamburguesa() {
    let boton = document.querySelector('.btn-menu-hamburguesa');
    let sidebar = document.querySelector('.sidebar');
    let overlay = document.querySelector('.sidebar-overlay');

    if (!boton || !sidebar) return;

    function abrir() {
      sidebar.classList.add('abierto');
      if (overlay) overlay.classList.add('visible');
      boton.setAttribute('aria-expanded', 'true');
    }

    function cerrar() {
      sidebar.classList.remove('abierto');
      if (overlay) overlay.classList.remove('visible');
      boton.setAttribute('aria-expanded', 'false');
    }

    boton.addEventListener('click', function () {
      if (sidebar.classList.contains('abierto')) {
        cerrar();
      } else {
        abrir();
      }
    });

    if (overlay) {
      overlay.addEventListener('click', cerrar);
    }

    document.addEventListener('keydown', function (evento) {
      if (evento.key === 'Escape') cerrar();
    });

    //Si se redimensiona a escritorio, cerrar el menú móvil.
    window.addEventListener('resize', function () {
      if (window.innerWidth > 768) cerrar();
    });
  }

  inicializarMenuUsuario();
  inicializarMenuHamburguesa();
})();
