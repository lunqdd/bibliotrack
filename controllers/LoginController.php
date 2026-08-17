<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class LoginController
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    public function index(): void
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: ' . $this->destinoSegunRol($_SESSION['usuario']['rol']));
            exit;
        }

        require __DIR__ . '/../views/login/login.php';
    }

    // POST ?controller=login&action=login (AJAX). Responde JSON con la sesión ya creada
    public function login(): void
    {
        header('Content-Type: application/json');

        $correo = trim($_POST['correo'] ?? '');
        $clave  = $_POST['clave'] ?? '';

        if (empty($correo) || empty($clave)) {
            echo json_encode(['response' => '01', 'message' => 'Debe ingresar el correo y la contraseña.']);
            return;
        }

        $usuario = $this->model->getByCorreo($correo);

        if (!$usuario || $usuario['estado'] !== 'activo' || !password_verify($clave, $usuario['password_hash'])) {
            echo json_encode(['response' => '01', 'message' => 'Correo o contraseña incorrectos.']);
            return;
        }

        $this->model->actualizarUltimoAcceso((int) $usuario['usuario_id']);

        $_SESSION['usuario'] = [
            'usuario_id'      => (int) $usuario['usuario_id'],
            'nombre_completo' => $usuario['nombre_completo'],
            'correo'          => $usuario['correo'],
            'rol'             => $usuario['rol'],
        ];

        echo json_encode([
            'response' => '00',
            'message'  => 'Inicio de sesión exitoso.',
            'redirect' => $this->destinoSegunRol($usuario['rol']),
        ]);
    }

    // POST ?controller=login&action=logout (AJAX). El redirect final lo hace el JS
    public function logout(): void
    {
        session_destroy();

        header('Content-Type: application/json');
        echo json_encode(['response' => '00']);
    }

    private function destinoSegunRol(string $rol): string
    {
        return $rol === 'admin'
            ? 'index.php?controller=dashboard&action=index'
            : 'index.php?controller=lector&action=index';
    }
}