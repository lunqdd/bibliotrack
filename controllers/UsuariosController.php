<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuariosController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new UsuarioModel();
    }

    // GET ?controller=usuarios&action=index. Listado con filtros por rol y estado
    public function index(): void
    {
        $usuarios = $this->model->getAll();

        $stats = [
            'total'          => count($usuarios),
            'activos'        => $this->model->contarActivos(),
            'conPrestamos'   => $this->model->contarConPrestamosActivos(),
            'nuevosEsteMes'  => $this->model->contarNuevosEsteMes(),
        ];

        require __DIR__ . '/../views/usuarios/usuarios.php';
    }

    // POST ?controller=usuarios&action=store (AJAX). Registro de un usuario nuevo
    public function store(): void
    {
        header('Content-Type: application/json');

        $data = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'identificacion'  => trim($_POST['identificacion'] ?? ''),
            'correo'          => trim($_POST['correo'] ?? ''),
            'telefono'        => trim($_POST['telefono'] ?? ''),
            'direccion'       => trim($_POST['direccion'] ?? ''),
            'rol'             => $_POST['rol'] ?? 'lector',
            'password'        => $_POST['password'] ?? '',
        ];

        if (empty($data['nombre_completo']) || empty($data['identificacion']) || empty($data['correo'])) {
            echo json_encode(['response' => '01', 'message' => 'Nombre, identificación y correo son obligatorios.']);
            return;
        }

        if (strlen($data['password']) < 8) {
            echo json_encode(['response' => '01', 'message' => 'La contraseña temporal debe tener al menos 8 caracteres.']);
            return;
        }

        $this->model->create($data);

        echo json_encode(['response' => '00', 'message' => 'Usuario registrado.']);
    }

    // POST ?controller=usuarios&action=update&id=X (AJAX). Edición administrativa (incluye rol y estado)
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        $data = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'identificacion'  => trim($_POST['identificacion'] ?? ''),
            'correo'          => trim($_POST['correo'] ?? ''),
            'telefono'        => trim($_POST['telefono'] ?? ''),
            'direccion'       => trim($_POST['direccion'] ?? ''),
            'rol'             => $_POST['rol'] ?? 'lector',
            'estado'          => $_POST['estado'] ?? 'activo',
        ];

        if (empty($data['nombre_completo']) || empty($data['identificacion']) || empty($data['correo'])) {
            echo json_encode(['response' => '01', 'message' => 'Nombre, identificación y correo son obligatorios.']);
            return;
        }

        $this->model->actualizar($id, $data);

        echo json_encode(['response' => '00', 'message' => 'Usuario actualizado.']);
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}
