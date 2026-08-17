<?php

require_once __DIR__ . '/../models/DashboardModel.php';

class InicioController
{
    public function index(): void
    {
        require __DIR__ . '/../views/inicio/index.php';
    }

    // GET ?controller=inicio&action=statsInicio (AJAX). Totales públicos de la portada
    public function statsJson(): void
    {
        $model = new DashboardModel();

        header('Content-Type: application/json');
        echo json_encode([
            'libros'    => $model->obtenerTotalLibros(),
            'prestamos' => $model->obtenerPrestamosActivos(),
            'usuarios'  => $model->obtenerUsuariosRegistrados(),
        ]);
    }
}