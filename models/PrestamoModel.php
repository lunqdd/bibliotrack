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

    // Historial completo con el estado calculado, para la gestión administrativa
    public function getAllAdmin(): array
    {
        $sql = "
            SELECT
                p.prestamo_id, p.codigo, p.fecha_prestamo, p.fecha_devolucion_programada,
                u.usuario_id, u.nombre_completo, u.codigo AS usuario_codigo,
                l.titulo, l.isbn,
                d.fecha_devolucion, d.estado_ejemplar, d.notas AS notas_devolucion
            FROM prestamos p
            INNER JOIN usuarios u ON p.usuario_id = u.usuario_id
            INNER JOIN ejemplares e ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l ON e.libro_id = l.libro_id
            LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            ORDER BY p.fecha_creacion DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Usuarios activos para el select del formulario de nuevo préstamo
    public function getUsuariosLectores(): array
    {
        return $this->db->query("SELECT usuario_id, nombre_completo, codigo FROM usuarios WHERE rol = 'lector' AND estado = 'activo' ORDER BY nombre_completo")->fetchAll();
    }

    // Libros con al menos un ejemplar disponible para prestar
    public function getLibrosDisponibles(): array
    {
        $sql = "
            SELECT l.libro_id, l.titulo, l.isbn
            FROM libros l
            INNER JOIN ejemplares e ON e.libro_id = l.libro_id AND e.estado = 'disponible'
            GROUP BY l.libro_id, l.titulo, l.isbn
            ORDER BY l.titulo
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Registra un préstamo nuevo tomando el primer ejemplar disponible del libro elegido
    public function registrarPrestamo(int $usuarioId, int $libroId, int $diasPlazo = 14): bool
    {
        $stmt = $this->db->prepare(
            "SELECT ejemplar_id FROM ejemplares WHERE libro_id = :libro_id AND estado = 'disponible' LIMIT 1"
        );
        $stmt->execute([':libro_id' => $libroId]);
        $ejemplarId = $stmt->fetchColumn();

        if (!$ejemplarId) {
            return false;
        }

        $this->db->prepare("UPDATE ejemplares SET estado = 'prestado' WHERE ejemplar_id = :id")
            ->execute([':id' => $ejemplarId]);

        $stmtPrestamo = $this->db->prepare(
            "INSERT INTO prestamos (codigo, ejemplar_id, usuario_id, fecha_devolucion_programada)
             VALUES (:codigo, :ejemplar_id, :usuario_id, DATE_ADD(CURDATE(), INTERVAL :dias DAY))"
        );

        return $stmtPrestamo->execute([
            ':codigo'      => $this->generarCodigo(),
            ':ejemplar_id' => $ejemplarId,
            ':usuario_id'  => $usuarioId,
            ':dias'        => $diasPlazo,
        ]);
    }

    // Cierra el préstamo con su devolución y libera el ejemplar (o lo manda a reparación)
    public function registrarDevolucion(int $prestamoId, string $estadoEjemplar, string $notas): bool
    {
        $stmt = $this->db->prepare('SELECT ejemplar_id FROM prestamos WHERE prestamo_id = :id');
        $stmt->execute([':id' => $prestamoId]);
        $ejemplarId = $stmt->fetchColumn();

        if (!$ejemplarId) {
            return false;
        }

        $stmtDevolucion = $this->db->prepare(
            "INSERT INTO devoluciones (prestamo_id, estado_ejemplar, notas) VALUES (:prestamo_id, :estado_ejemplar, :notas)"
        );
        $registrada = $stmtDevolucion->execute([
            ':prestamo_id'    => $prestamoId,
            ':estado_ejemplar' => $estadoEjemplar,
            ':notas'          => $notas,
        ]);

        if (!$registrada) {
            return false;
        }

        $nuevoEstado = $estadoEjemplar === 'requiere_reparacion' ? 'en_reparacion' : 'disponible';
        $this->db->prepare('UPDATE ejemplares SET estado = :estado WHERE ejemplar_id = :id')
            ->execute([':estado' => $nuevoEstado, ':id' => $ejemplarId]);

        return true;
    }

    public function contarPrestamosActivos(): int
    {
        $sql = "SELECT COUNT(*) FROM prestamos p LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id WHERE d.devolucion_id IS NULL";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    public function contarVencidos(): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM prestamos p
            LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            WHERE d.devolucion_id IS NULL AND p.fecha_devolucion_programada < CURDATE()
        ";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    public function contarDevoluciones(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM devoluciones')->fetchColumn();
    }

    public function contarTodos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM prestamos')->fetchColumn();
    }

    private function generarCodigo(): string
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM prestamos')->fetchColumn();
        return 'PRE-' . str_pad((string) ($total + 1), 3, '0', STR_PAD_LEFT);
    }
}