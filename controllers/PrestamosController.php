<?php

require_once __DIR__ . '/../models/PrestamoModel.php';

class PrestamosController
{
    private PrestamoModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new PrestamoModel();
    }

    // GET ?controller=prestamos&action=index. Historial completo con estado calculado
    public function index(): void
    {
        $prestamos = $this->model->getAllAdmin();

        $stats = [
            'activos'      => $this->model->contarPrestamosActivos(),
            'devoluciones' => $this->model->contarDevoluciones(),
            'vencidos'     => $this->model->contarVencidos(),
            'tasaRetorno'  => $this->model->contarTodos() > 0
                ? round(($this->model->contarDevoluciones() / $this->model->contarTodos()) * 100)
                : 0,
        ];

        $usuarios = $this->model->getUsuariosLectores();
        $libros   = $this->model->getLibrosDisponibles();

        require __DIR__ . '/../views/prestamos/prestamos.php';
    }

    // POST ?controller=prestamos&action=store (AJAX). Toma el primer ejemplar disponible del libro elegido
    public function store(): void
    {
        header('Content-Type: application/json');

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $libroId   = (int) ($_POST['libro_id'] ?? 0);

        if ($usuarioId <= 0 || $libroId <= 0) {
            echo json_encode(['response' => '01', 'message' => 'Selecciona un usuario y un libro válidos.']);
            return;
        }

        if (!$this->model->registrarPrestamo($usuarioId, $libroId)) {
            echo json_encode(['response' => '01', 'message' => 'No hay ejemplares disponibles de ese libro.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Préstamo registrado.']);
    }

    // POST ?controller=prestamos&action=registrarDevolucion (AJAX). Cierra un préstamo activo
    public function registrarDevolucion(): void
    {
        header('Content-Type: application/json');

        $prestamoId = (int) ($_POST['prestamo_id'] ?? 0);
        $estadoEjemplar = $_POST['estado_ejemplar'] ?? 'buen_estado';
        $notas = trim($_POST['notas'] ?? '');

        if ($prestamoId <= 0) {
            echo json_encode(['response' => '01', 'message' => 'Selecciona un préstamo válido.']);
            return;
        }

        if (!$this->model->registrarDevolucion($prestamoId, $estadoEjemplar, $notas)) {
            echo json_encode(['response' => '01', 'message' => 'No se pudo registrar la devolución.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Devolución registrada.']);
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}
