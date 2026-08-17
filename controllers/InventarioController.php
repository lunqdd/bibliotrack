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

    // GET ?controller=inventario&action=index. Existencias por ubicación física
    public function index(): void
    {
        $busqueda = trim($_GET['q'] ?? '');
        $estado   = trim($_GET['estado'] ?? '');

        $inventario = $this->model->getListado($busqueda, $estado);
        $stats      = $this->model->obtenerStats();

        require __DIR__ . '/../views/inventario/inventario.php';
    }

    // GET ?controller=inventario&action=buscarInventario (AJAX). Listado filtrado en JSON
    public function buscarInventario(): void
    {
        $busqueda = trim($_GET['q'] ?? '');
        $estado   = trim($_GET['estado'] ?? '');

        header('Content-Type: application/json');
        echo json_encode(['inventario' => $this->model->getListado($busqueda, $estado)]);
    }

    // GET ?controller=inventario&action=edit&id= (AJAX). Datos de una ubicación para el modal de edición
    public function edit(int $id): void
    {
        header('Content-Type: application/json');
        $fila = $this->model->getByLibroId($id);

        if (!$fila) {
            echo json_encode(['response' => '01', 'message' => 'Registro no encontrado.']);
            return;
        }

        echo json_encode(['response' => '00', 'item' => $fila]);
    }

    // POST ?controller=inventario&action=update&id= (AJAX). Reubica el libro y ajusta la estantería
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        $pasillo   = trim($_POST['pasillo'] ?? '');
        $estante   = trim($_POST['estante'] ?? '');
        $enEstante = (int) ($_POST['en_estante'] ?? -1);

        if ($pasillo === '' || $estante === '' || $enEstante < 0) {
            echo json_encode(['response' => '01', 'message' => 'Completa el pasillo, el estante y la cantidad en estante.']);
            return;
        }

        $actualizado = $this->model->actualizarUbicacion($id, $pasillo, $estante, $enEstante);

        if (!$actualizado) {
            echo json_encode(['response' => '01', 'message' => 'La cantidad en estante no puede superar los ejemplares que no están prestados.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Ubicación actualizada.']);
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}
