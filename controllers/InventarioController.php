<?php

require_once __DIR__ . '/../models/InventarioModel.php';

class InventarioController
{
    private InventarioModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new InventarioModel();
    }

    // GET ?controller=inventario&action=index. Existencias por libro y ubicación
    public function index(): void
    {
        $secciones = $this->model->getResumen();

        $totalEjemplares = $this->model->contarTotalEjemplares();
        $disponibles     = $this->model->contarPorEstado('disponible');
        $prestados       = $this->model->contarPorEstado('prestado');
        $enReparacion    = $this->model->contarPorEstado('en_reparacion');

        $stats = [
            'totalEjemplares' => $totalEjemplares,
            'disponibles'     => $disponibles,
            'prestados'       => $prestados,
            'enReparacion'    => $enReparacion,
            'porcentajeDisponibles' => $totalEjemplares > 0 ? round(($disponibles / $totalEjemplares) * 100) : 0,
            'porcentajePrestados'   => $totalEjemplares > 0 ? round(($prestados / $totalEjemplares) * 100) : 0,
        ];

        require __DIR__ . '/../views/inventario/inventario.php';
    }

    // POST ?controller=inventario&action=actualizarInventario (AJAX). Mueve una sección y ajusta ejemplares en estante
    public function actualizarInventario(): void
    {
        header('Content-Type: application/json');

        $libroId     = (int) ($_POST['libro_id'] ?? 0);
        $ubicacionId = (int) ($_POST['ubicacion_id'] ?? 0);
        $pasillo     = trim($_POST['pasillo'] ?? '');
        $estante     = trim($_POST['estante'] ?? '');
        $enEstante   = (int) ($_POST['en_estante'] ?? -1);

        if ($libroId <= 0 || $ubicacionId <= 0 || empty($pasillo) || empty($estante) || $enEstante < 0) {
            echo json_encode(['response' => '01', 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        $this->model->actualizarSeccion($libroId, $ubicacionId, $pasillo, $estante, $enEstante);

        echo json_encode(['response' => '00', 'message' => 'Inventario actualizado.']);
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}
