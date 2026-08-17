<?php
$pageTitle = 'Inicio';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | BiblioTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
</head>

<body>

    <main class="portada">

        <section class="portada-contenido">
            <div class="portada-texto">

                <img src="img/logo-color.png" alt="BiblioTrack" class="portada-logo">
                <h1>Biblioteca organizada, préstamos claros.</h1>

                <p>
                    BiblioTrack ayuda a registrar libros, usuarios,
                    préstamos y devoluciones de una biblioteca pública
                    desde una interfaz sencilla.
                </p>

                <div class="portada-acciones" aria-label="Accesos principales">
                    <a href="index.php?controller=login&action=index" class="btn btn-primario">
                        Iniciar sesión
                    </a>
                </div>
            </div>

            <div class="portada-panel" aria-label="Resumen del sistema">

                <div class="portada-dato">
                    <span>Libros</span>
                    <strong id="dato-libros">0</strong>
                </div>

                <div class="portada-dato">
                    <span>Préstamos activos</span>
                    <strong id="dato-prestamos">0</strong>
                </div>

                <div class="portada-dato">
                    <span>Usuarios registrados</span>
                    <strong id="dato-usuarios">0</strong>
                </div>
            </div>
        </section>
    </main>
    <script src="js/inicio.js"></script>
</body>
</html>