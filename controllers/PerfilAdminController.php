<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class PerfilAdminController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new UsuarioModel();
    }

    // GET ?controller=perfilAdmin&action=index. Ficha con los datos de la cuenta administrativa activa
    public function index(): void
    {
        $usuarioId = $_SESSION['usuario']['usuario_id'];
        $usuario   = $this->model->getById($usuarioId);

        require __DIR__ . '/../views/admin/perfiladmin.php';
    }

    // POST ?controller=perfilAdmin&action=actualizarPerfil (AJAX). Edición de datos personales
    public function actualizarPerfil(): void
    {
        header('Content-Type: application/json');

        $usuarioId = $_SESSION['usuario']['usuario_id'];

        $data = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'correo'          => trim($_POST['correo'] ?? ''),
            'telefono'        => trim($_POST['telefono'] ?? ''),
            'direccion'       => trim($_POST['direccion'] ?? ''),
        ];

        if (empty($data['nombre_completo']) || empty($data['correo'])) {
            echo json_encode(['response' => '01', 'message' => 'El nombre y el correo son obligatorios.']);
            return;
        }

        $this->model->actualizarPerfil($usuarioId, $data);

        $_SESSION['usuario']['nombre_completo'] = $data['nombre_completo'];
        $_SESSION['usuario']['correo']          = $data['correo'];

        echo json_encode(['response' => '00', 'message' => 'Perfil actualizado.', 'usuario' => $data]);
    }

    // POST ?controller=perfilAdmin&action=cambiarClave (AJAX). Cambio de contraseña
    public function cambiarClave(): void
    {
        header('Content-Type: application/json');

        $usuarioId = $_SESSION['usuario']['usuario_id'];

        $actual    = $_POST['clave_actual'] ?? '';
        $nueva     = $_POST['clave_nueva'] ?? '';
        $confirmar = $_POST['clave_confirmar'] ?? '';

        $usuario = $this->model->getById($usuarioId);

        if (!password_verify($actual, $usuario['password_hash'])) {
            echo json_encode(['response' => '01', 'message' => 'La contraseña actual no es correcta.']);
            return;
        }

        if (strlen($nueva) < 8) {
            echo json_encode(['response' => '01', 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
            return;
        }

        if ($nueva !== $confirmar) {
            echo json_encode(['response' => '01', 'message' => 'Las contraseñas no coinciden.']);
            return;
        }

        $this->model->actualizarClave($usuarioId, password_hash($nueva, PASSWORD_DEFAULT));

        echo json_encode(['response' => '00', 'message' => 'Contraseña actualizada.']);
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}