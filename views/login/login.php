<?php
$pageTitle = "Iniciar Sesión";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?= $pageTitle ?> | BiblioTrack </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
</head>

<body>

    <main class="pagina-login">
        <div class="card login-card">
            <div class="login-icono">
                <img src="img/logo-color.png" alt="BiblioTrack" class="login-logo">
            </div>

            <div class="mensaje-error-servidor" id="mensaje-error-login" style="display:none"></div>

            <form id="formulario-login" method="POST" action="index.php?controller=login&action=login" novalidate>
                <div class="campo-grupo">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="usuario@ufide.ac.cr" autocomplete="email"
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                    <span class="mensaje-error" id="error-correo"></span>
                </div>

                <div class="campo-grupo">
                    <label for="clave">Contraseña</label>
                    <input type="password" id="clave" name="clave" placeholder="••••••••"
                        autocomplete="current-password">
                    <span class="mensaje-error" id="error-clave"></span>
                </div>

                <button type="submit" class="btn btn-primario btn-bloque" id="btn-login" disabled>
                    Iniciar sesión
                </button>
                <div class="mensaje-exito" id="mensaje-exito-login">
                </div>
            </form>
        </div>
    </main>
    <script src="js/login.js"></script>
</body>
</html>