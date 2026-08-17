<?php

require_once __DIR__ . '/../config/database.php';

class ReporteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Préstamos y devoluciones por mes, de enero al mes actual del año en curso
    public function obtenerFlujoMensual(): array
    {
        $sqlPrestados = "
            SELECT MONTH(fecha_prestamo) AS mes, COUNT(*) AS cantidad
            FROM prestamos
            WHERE YEAR(fecha_prestamo) = YEAR(CURDATE())
            GROUP BY MONTH(fecha_prestamo)
        ";
        $sqlDevueltos = "
            SELECT MONTH(fecha_devolucion) AS mes, COUNT(*) AS cantidad
            FROM devoluciones
            WHERE YEAR(fecha_devolucion) = YEAR(CURDATE())
            GROUP BY MONTH(fecha_devolucion)
        ";

        $prestadosPorMes = [];
        foreach ($this->db->query($sqlPrestados)->fetchAll() as $fila) {
            $prestadosPorMes[(int) $fila['mes']] = (int) $fila['cantidad'];
        }
        $devueltosPorMes = [];
        foreach ($this->db->query($sqlDevueltos)->fetchAll() as $fila) {
            $devueltosPorMes[(int) $fila['mes']] = (int) $fila['cantidad'];
        }

        $nombresMes = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $mesActual = (int) date('n');

        $flujo = [];
        for ($mes = 1; $mes <= $mesActual; $mes++) {
            $flujo[] = [
                'mes'       => $nombresMes[$mes - 1],
                'prestados' => $prestadosPorMes[$mes] ?? 0,
                'devueltos' => $devueltosPorMes[$mes] ?? 0,
            ];
        }

        return $flujo;
    }

    public function obtenerLibrosMasPrestados(int $limite = 5): array
    {
        $sql = "
            SELECT l.titulo AS nombre, COUNT(p.prestamo_id) AS prestamos
            FROM prestamos p
            INNER JOIN ejemplares e ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l ON e.libro_id = l.libro_id
            GROUP BY l.libro_id, l.titulo
            ORDER BY prestamos DESC
            LIMIT " . (int) $limite . "
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerUsuariosMasActivos(int $limite = 5): array
    {
        $sql = "
            SELECT u.nombre_completo AS nombre, COUNT(p.prestamo_id) AS prestamos
            FROM usuarios u
            LEFT JOIN prestamos p ON p.usuario_id = u.usuario_id
            WHERE u.rol = 'lector'
            GROUP BY u.usuario_id, u.nombre_completo
            ORDER BY prestamos DESC
            LIMIT " . (int) $limite . "
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Cuenta las secciones de inventario (libro + ubicación) según su disponibilidad
    public function obtenerEstadoInventario(): array
    {
        $sql = "
            SELECT
                COUNT(CASE WHEN en_estante = 0 THEN 1 END) AS agotado,
                COUNT(CASE WHEN en_estante > 0 AND en_estante < totales THEN 1 END) AS parcial,
                COUNT(CASE WHEN en_estante = totales THEN 1 END) AS completo
            FROM (
                SELECT
                    COUNT(e.ejemplar_id) AS totales,
                    COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS en_estante
                FROM ejemplares e
                GROUP BY e.libro_id, e.ubicacion_id
            ) secciones
        ";
        return $this->db->query($sql)->fetch();
    }

    public function obtenerTasaRetorno(): float
    {
        $totalPrestamos = (int) $this->db->query('SELECT COUNT(*) FROM prestamos')->fetchColumn();
        $totalDevoluciones = (int) $this->db->query('SELECT COUNT(*) FROM devoluciones')->fetchColumn();

        return $totalPrestamos > 0 ? round(($totalDevoluciones / $totalPrestamos) * 100) : 0;
    }
}
