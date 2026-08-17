//Validación básica del inicio de sesión

$(function () {

  let formulario = $("#formulario-login");
  let campoCorreo = $("#correo");
  let campoClave = $("#clave");
  let btnLogin = $("#btn-login");
  let errorCorreo = $("#error-correo");
  let errorClave = $("#error-clave");
  let errorServidor = $("#mensaje-error-login");

  let regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  //Aplica el estado de válido o inválido a un campo y muestra el mensaje
  function validarCampo(campo, elementoError, mensaje, condicionValida) {
    if (!condicionValida) {
      campo.addClass("campo-invalido").removeClass("campo-valido");
      elementoError.text(mensaje).show();
      return false;
    }

    campo.removeClass("campo-invalido").addClass("campo-valido");
    elementoError.text("").hide();
    return true;
  }

  //Comprueba que el correo no esté vacío y que cumpla el formato esperado
  function validarCorreo() {
    let valor = campoCorreo.val().trim();

    if (valor.length === 0) {
      return validarCampo(campoCorreo, errorCorreo, "El correo electrónico es obligatorio.", false);
    }

    return validarCampo(campoCorreo, errorCorreo, "Ingrese un correo electrónico válido.", regexCorreo.test(valor));
  }

  //Verifica que la contraseña tenga la longitud mínima requerida
  function validarClave() {
    let valor = campoClave.val();

    if (valor.length === 0) {
      return validarCampo(campoClave, errorClave, "La contraseña es obligatoria.", false);
    }

    return validarCampo(campoClave, errorClave, "La contraseña debe tener al menos 6 caracteres.", valor.length >= 6);
  }

  //Habilita o deshabilita el botón de envío según el estado de los campos
  function actualizarBoton() {
    let correoValido = regexCorreo.test(campoCorreo.val().trim());
    let claveValida = campoClave.val().length >= 6;
    btnLogin.prop("disabled", !(correoValido && claveValida));
  }

  campoCorreo.on("input", function () {
    validarCorreo();
    actualizarBoton();
  });

  campoClave.on("input", function () {
    validarClave();
    actualizarBoton();
  });

  //Valida en el cliente y, si pasa, envía el login por AJAX
  formulario.on("submit", function (evento) {
    evento.preventDefault();

    let formularioValido = validarCorreo() && validarClave();
    if (!formularioValido) {
      return;
    }

    errorServidor.hide().text("");
    btnLogin.prop("disabled", true);

    $.post(formulario.attr("action"), {
      correo: campoCorreo.val().trim(),
      clave: campoClave.val(),
    }, function (data) {
      if (data.response === "00") {
        window.location = data.redirect;
      } else {
        errorServidor.text(data.message).show();
        btnLogin.prop("disabled", false);
      }
    }, "json");
  });

});
