<?php

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/LibroModel.php';
require_once __DIR__ . '/../models/PrestamoModel.php';
require_once __DIR__ . '/../models/SolicitudModel.php';

class LectorController
{
    private UsuarioModel $usuarioModel;
    private LibroModel $libroModel;
    private PrestamoModel $prestamoModel;
    private SolicitudModel $solicitudModel;

    public function __construct()
    {
        $this->requerirLector();

        $this->usuarioModel   = new UsuarioModel();
        $this->libroModel     = new LibroModel();
        $this->prestamoModel  = new PrestamoModel();
        $this->solicitudModel = new SolicitudModel();
    }

    // GET ?controller=lector&action=index. Panel principal del lector
    public function index(): void
    {
        $usuarioId = $_SESSION['usuario']['usuario_id'];

        $stats = [
            'librosEnCasa'     => $this->prestamoModel->contarActivos($usuarioId),
            'proximaEntrega'   => $this->prestamoModel->proximaFechaEntrega($usuarioId),
            'totalPrestamos'   => $this->prestamoModel->contarTotal($usuarioId),
            'devolucionesAnio' => $this->prestamoModel->contarDevolucionesAnioActual($usuarioId),
        ];

        $prestamosActivos = $this->prestamoModel->getActivosByUsuario($usuarioId);
        $librosDisponibles = $this->libroModel->getDisponibles();
        $recomendados = $this->libroModel->getRecomendados($usuarioId);

        require __DIR__ . '/../views/lector/lector.php';
    }

    // GET ?controller=lector&action=explorar. Catálogo con búsqueda y filtro por género
    public function explorar(): void
    {
        $busqueda = trim($_GET['q'] ?? '');
        $generoId = isset($_GET['genero']) && $_GET['genero'] !== '' ? (int) $_GET['genero'] : null;

        $catalogo = $this->libroModel->getCatalogo($busqueda, $generoId);
        $generos  = $this->libroModel->getGeneros();

        require __DIR__ . '/../views/lector/explorar.php';
    }

    // GET ?controller=lector&action=buscarCatalogo (AJAX). Catálogo filtrado en JSON
    public function buscarCatalogo(): void
    {
        $busqueda = trim($_GET['q'] ?? '');
        $generoId = isset($_GET['genero']) && $_GET['genero'] !== '' ? (int) $_GET['genero'] : null;

        header('Content-Type: application/json');
        echo json_encode(['catalogo' => $this->libroModel->getCatalogo($busqueda, $generoId)]);
    }

    // GET ?controller=lector&action=misPrestamos. Pestañas de activos e historial
    public function misPrestamos(): void
    {
        $usuarioId = $_SESSION['usuario']['usuario_id'];

        $activos   = $this->prestamoModel->getActivosByUsuario($usuarioId);
        $historial = $this->prestamoModel->getHistorialByUsuario($usuarioId);

        require __DIR__ . '/../views/lector/mis-prestamos.php';
    }

    // GET ?controller=lector&action=perfil. Datos personales y formularios de edición
    public function perfil(): void
    {
        $usuarioId      = $_SESSION['usuario']['usuario_id'];
        $usuario        = $this->usuarioModel->getById($usuarioId);
        $totalPrestamos = $this->prestamoModel->contarTotal($usuarioId);
        $devoluciones   = $this->prestamoModel->contarDevolucionesAnioActual($usuarioId);

        require __DIR__ . '/../views/lector/perfil.php';
    }

    // POST ?controller=lector&action=solicitar (AJAX). Solicitud de préstamo desde el catálogo o el panel
    public function solicitar(): void
    {
        header('Content-Type: application/json');

        $usuarioId   = $_SESSION['usuario']['usuario_id'];
        $libroId     = (int) ($_POST['libro_id'] ?? 0);
        $fechaRetiro = trim($_POST['fecha'] ?? '');
        $notas       = trim($_POST['notas'] ?? '');

        if ($libroId <= 0) {
            echo json_encode(['response' => '01', 'message' => 'Selecciona un libro válido.']);
            return;
        }

        if ($fechaRetiro !== '') {
            $notas = trim('Fecha de retiro deseada: ' . $fechaRetiro . '. ' . $notas);
        }

        $this->solicitudModel->create($usuarioId, $libroId, $notas);

        echo json_encode(['response' => '00', 'message' => 'Solicitud registrada. Queda pendiente de aprobación.']);
    }

    // POST ?controller=lector&action=actualizarPerfil (AJAX). Edición de datos personales
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

    // POST ?controller=lector&action=cambiarClave (AJAX). Cambio de contraseña
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

    private function requerirLector(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'lector') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}