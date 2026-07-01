//Validación básica del inicio de sesión

(function () {
  'use strict';

  //Referencias a los elementos del formulario y a los mensajes de error
  let formulario = document.getElementById('formulario-login');
  let campoCorreo = document.getElementById('correo');
  let campoClave = document.getElementById('clave');
  let btnLogin = document.getElementById('btn-login');
  let errorCorreo = document.getElementById('error-correo');
  let errorClave = document.getElementById('error-clave');
  let mensajeExito = document.getElementById('mensaje-exito-login');

  //Patrón mínimo para comprobar el formato del correo
  let regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  //Aplica el estado de válido o inválido a un campo y muestra el mensaje
  function validarCampo(campo, elementoError, mensaje, condicionValida) {
    if (!condicionValida) {
      campo.classList.add('campo-invalido');
      campo.classList.remove('campo-valido');
      elementoError.textContent = mensaje;
      elementoError.style.display = 'block';
      return false;
    }

    campo.classList.remove('campo-invalido');
    campo.classList.add('campo-valido');
    elementoError.textContent = '';
    elementoError.style.display = 'none';
    return true;
  }

  //Comprueba que el correo no esté vacío y que cumpla el formato esperado
  function validarCorreo() {
    let valor = campoCorreo.value.trim();

    if (valor.length === 0) {
      return validarCampo(campoCorreo, errorCorreo, 'El correo electrónico es obligatorio.', false);
    }

    return validarCampo(campoCorreo, errorCorreo, 'Ingrese un correo electrónico válido.', regexCorreo.test(valor));
  }

  //Verifica que la contraseña tenga la longitud mínima requerida
  function validarClave() {
    let valor = campoClave.value;

    if (valor.length === 0) {
      return validarCampo(campoClave, errorClave, 'La contraseña es obligatoria.', false);
    }

    return validarCampo(campoClave, errorClave, 'La contraseña debe tener al menos 6 caracteres.', valor.length >= 6);
  }

  //Habilita o deshabilita el botón de envío según el estado de los campos
  function actualizarBoton() {
    let correoValido = regexCorreo.test(campoCorreo.value.trim());
    let claveValida = campoClave.value.length >= 6;

    btnLogin.disabled = !(correoValido && claveValida);
  }

  //Simula el destino por rol hasta que PHP haga la autenticación real
  function destinoPorCorreo(correo) {
    return correo === 'ldelgado00034@ufide.ac.cr' ? 'dashboard.html' : 'lector.html';
  }

  //Reacciona a los cambios en el campo de correo
  campoCorreo.addEventListener('input', function () {
    validarCorreo();
    actualizarBoton();
  });

  //Reacciona a los cambios en el campo de contraseña
  campoClave.addEventListener('input', function () {
    validarClave();
    actualizarBoton();
  });

  //Valida el formulario completo antes de permitir el envío
  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();

    if (validarCorreo() && validarClave()) {
      mensajeExito.textContent = 'Inicio de sesión correcto. Redirigiendo...';
      mensajeExito.style.display = 'block';

      setTimeout(function () {
        window.location.href = destinoPorCorreo(campoCorreo.value.trim().toLowerCase());
      }, 600);
    }
  });
})();
