<?php

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController
{
    private DashboardModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new DashboardModel();
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