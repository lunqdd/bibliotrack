<?php

require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/PrestamoModel.php';

class ReportesController
{
    private ReporteModel $model;
    private DashboardModel $dashboardModel;
    private PrestamoModel $prestamoModel;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new ReporteModel();
        $this->dashboardModel = new DashboardModel();
        $this->prestamoModel = new PrestamoModel();
    }

    // GET ?controller=reportes&action=index. Circulación, usuarios más activos e inventario
    public function index(): void
    {
        $stats = [
            'totalPrestamos'        => $this->prestamoModel->contarTodos(),
            'tasaRetorno'           => $this->model->obtenerTasaRetorno(),
            'usuariosRegistrados'   => $this->dashboardModel->obtenerUsuariosRegistrados(),
            'ejemplaresCirculacion' => $this->prestamoModel->contarPrestamosActivos(),
        ];

        $flujoMensual        = $this->model->obtenerFlujoMensual();
        $librosMasPrestados   = $this->model->obtenerLibrosMasPrestados();
        $usuariosMasActivos   = $this->model->obtenerUsuariosMasActivos();
        $estadoInventario     = $this->model->obtenerEstadoInventario();

        require __DIR__ . '/../views/reportes/reportes.php';
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}
