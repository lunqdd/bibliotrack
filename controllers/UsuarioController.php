<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new UsuarioModel();
    }

    // GET ?controller=usuario&action=index. Listado administrable de usuarios
    public function index(): void
    {
        [$busqueda, $rol, $estado] = $this->filtros();

        $usuarios = $this->model->getAdminListado($busqueda, $rol, $estado);
        $stats    = $this->model->obtenerStats();

        require __DIR__ . '/../views/usuarios/usuarios.php';
    }

    // GET ?controller=usuario&action=buscarUsuarios (AJAX). Listado filtrado en JSON
    public function buscarUsuarios(): void
    {
        [$busqueda, $rol, $estado] = $this->filtros();

        header('Content-Type: application/json');
        echo json_encode(['usuarios' => $this->model->getAdminListado($busqueda, $rol, $estado)]);
    }

    // GET ?controller=usuario&action=edit&id= (AJAX). Datos de un usuario para ver detalle o precargar edición
    public function edit(int $id): void
    {
        header('Content-Type: application/json');
        $usuario = $this->model->getById($id);

        if (!$usuario) {
            echo json_encode(['response' => '01', 'message' => 'Usuario no encontrado.']);
            return;
        }

        unset($usuario['password_hash']);
        echo json_encode(['response' => '00', 'usuario' => $usuario]);
    }

    // POST ?controller=usuario&action=store (AJAX). Registra un usuario nuevo
    public function store(): void
    {
        header('Content-Type: application/json');

        $data = $this->datosFormulario();

        if ($data['nombre_completo'] === '' || $data['identificacion'] === '' || $data['correo'] === '') {
            echo json_encode(['response' => '01', 'message' => 'El nombre, la identificación y el correo son obligatorios.']);
            return;
        }

        try {
            $this->model->create($data);
        } catch (PDOException $e) {
            echo json_encode(['response' => '01', 'message' => 'No se pudo registrar el usuario. Verifica que el correo y la identificación no estén repetidos.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Usuario registrado. La contraseña temporal es su número de identificación.']);
    }

    // POST ?controller=usuario&action=update&id= (AJAX). Actualiza los datos de un usuario
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        $data = $this->datosFormulario();

        if ($data['nombre_completo'] === '' || $data['identificacion'] === '' || $data['correo'] === '') {
            echo json_encode(['response' => '01', 'message' => 'El nombre, la identificación y el correo son obligatorios.']);
            return;
        }

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            echo json_encode(['response' => '01', 'message' => 'No se pudo actualizar el usuario. Verifica que el correo y la identificación no estén repetidos.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Usuario actualizado.']);
    }

    private function filtros(): array
    {
        return [
            trim($_GET['q'] ?? ''),
            trim($_GET['rol'] ?? ''),
            trim($_GET['estado'] ?? ''),
        ];
    }

    private function datosFormulario(): array
    {
        $rol = $_POST['rol'] ?? '';

        return [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'identificacion'  => trim($_POST['identificacion'] ?? ''),
            'correo'          => trim($_POST['correo'] ?? ''),
            'telefono'        => trim($_POST['telefono'] ?? ''),
            'direccion'       => trim($_POST['direccion'] ?? ''),
            'rol'             => in_array($rol, ['admin', 'lector'], true) ? $rol : 'lector',
        ];
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}