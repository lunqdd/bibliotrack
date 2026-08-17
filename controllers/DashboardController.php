<?php

require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class DashboardController
{
    private DashboardModel $model;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new DashboardModel();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index(): void
    {
        // Tarjetas superiores del dashboard
        $stats = [
            "libros" => $this->model->obtenerTotalLibros(),
            "ejemplares" => $this->model->obtenerTotalEjemplares(),
            "prestamos" => $this->model->obtenerPrestamosActivos(),
            "usuarios" => $this->model->obtenerUsuariosRegistrados(),
            "tardias" => $this->model->obtenerDevolucionesTardias()
        ];

        // Tabla actividad reciente
        $actividad = $this->model->obtenerActividadReciente();

        // Ranking de libros
        $librosPopulares = $this->model->obtenerLibrosPopulares();

        // Gráfico semanal: se completan los 7 días aunque no tengan préstamos
        $tendencia = $this->construirTendenciaSemanal($this->model->obtenerTendenciaSemanal());

        require __DIR__ . '/../views/dashboard/dashboard.php';
    }

    // GET ?controller=dashboard&action=actividadReciente (AJAX). Refresca la tabla sin recargar
    public function actividadJson(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['actividad' => $this->model->obtenerActividadReciente()]);
    }

    // GET ?controller=dashboard&action=perfil. Perfil de la cuenta administrativa
    public function perfil(): void
    {
        $usuarioId = $_SESSION['usuario']['usuario_id'];
        $usuario   = $this->usuarioModel->getById($usuarioId);

        require __DIR__ . '/../views/admin/perfil-admin.php';
    }

    // POST ?controller=dashboard&action=actualizarPerfil (AJAX). Edición de datos personales
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

        $this->usuarioModel->actualizarPerfil($usuarioId, $data);

        $_SESSION['usuario']['nombre_completo'] = $data['nombre_completo'];
        $_SESSION['usuario']['correo']          = $data['correo'];

        echo json_encode(['response' => '00', 'message' => 'Perfil actualizado.', 'usuario' => $data]);
    }

    // POST ?controller=dashboard&action=cambiarClave (AJAX). Cambio de contraseña
    public function cambiarClave(): void
    {
        header('Content-Type: application/json');

        $usuarioId = $_SESSION['usuario']['usuario_id'];

        $actual    = $_POST['clave_actual'] ?? '';
        $nueva     = $_POST['clave_nueva'] ?? '';
        $confirmar = $_POST['clave_confirmar'] ?? '';

        $usuario = $this->usuarioModel->getById($usuarioId);

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

        $this->usuarioModel->actualizarClave($usuarioId, password_hash($nueva, PASSWORD_DEFAULT));

        echo json_encode(['response' => '00', 'message' => 'Contraseña actualizada.']);
    }

    // Rellena los 7 días de la semana en orden, usando 0 donde no hubo préstamos
    private function construirTendenciaSemanal(array $filas): array
    {
        $diasEs = [
            'Sunday' => 'DOM', 'Monday' => 'LUN', 'Tuesday' => 'MAR', 'Wednesday' => 'MIÉ',
            'Thursday' => 'JUE', 'Friday' => 'VIE', 'Saturday' => 'SÁB',
        ];

        $conteoPorDia = [];
        foreach ($filas as $fila) {
            $conteoPorDia[$fila['dia']] = (int) $fila['cantidad'];
        }

        $tendencia = [];
        for ($i = 6; $i >= 0; $i--) {
            $nombreIngles = (new DateTime("-{$i} days"))->format('l');
            $tendencia[] = [
                'dia'   => $diasEs[$nombreIngles],
                'valor' => $conteoPorDia[$nombreIngles] ?? 0,
                'hoy'   => $i === 0,
            ];
        }

        return $tendencia;
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}