<?php

require_once __DIR__ . '/../config/database.php';

class PrestamoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Préstamos vigentes (sin devolución) de un lector
    public function getActivosByUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT
                l.titulo,
                a.nombre AS autor,
                p.fecha_prestamo,
                p.fecha_devolucion_programada
            FROM prestamos p
            INNER JOIN ejemplares e ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l ON e.libro_id = l.libro_id
            INNER JOIN autores a ON l.autor_id = a.autor_id
            LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            WHERE p.usuario_id = :usuario_id AND d.devolucion_id IS NULL
            ORDER BY p.fecha_devolucion_programada ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    // Préstamos ya cerrados (con devolución) de un lector
    public function getHistorialByUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT
                l.titulo,
                a.nombre AS autor,
                p.fecha_prestamo,
                d.fecha_devolucion
            FROM prestamos p
            INNER JOIN ejemplares e ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l ON e.libro_id = l.libro_id
            INNER JOIN autores a ON l.autor_id = a.autor_id
            INNER JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            WHERE p.usuario_id = :usuario_id
            ORDER BY d.fecha_devolucion DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function contarActivos(int $usuarioId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM prestamos p
            LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            WHERE p.usuario_id = :usuario_id AND d.devolucion_id IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    public function contarTotal(int $usuarioId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM prestamos WHERE usuario_id = :usuario_id');
        $stmt->execute([':usuario_id' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    public function contarDevolucionesAnioActual(int $usuarioId): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM devoluciones d
            INNER JOIN prestamos p ON d.prestamo_id = p.prestamo_id
            WHERE p.usuario_id = :usuario_id AND YEAR(d.fecha_devolucion) = YEAR(CURDATE())
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    // Fecha de vencimiento más próxima entre los préstamos activos
    public function proximaFechaEntrega(int $usuarioId): ?string
    {
        $sql = "
            SELECT MIN(p.fecha_devolucion_programada) AS proxima
            FROM prestamos p
            LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            WHERE p.usuario_id = :usuario_id AND d.devolucion_id IS NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        $resultado = $stmt->fetchColumn();
        return $resultado ?: null;
    }
}