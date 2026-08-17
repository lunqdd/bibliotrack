<?php

require_once __DIR__ . '/../config/database.php';

class SolicitudModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Registra la solicitud de préstamo hecha por el lector desde el catálogo
    public function create(int $usuarioId, int $libroId, string $notas = ''): bool
    {
        $codigo = 'SOL-' . str_pad((string) $this->siguienteConsecutivo(), 3, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare(
            'INSERT INTO solicitudes_prestamo (codigo, usuario_id, libro_id, notas)
             VALUES (:codigo, :usuario_id, :libro_id, :notas)'
        );

        return $stmt->execute([
            ':codigo'     => $codigo,
            ':usuario_id' => $usuarioId,
            ':libro_id'   => $libroId,
            ':notas'      => $notas,
        ]);
    }

    // Solicitudes a la espera de que el admin las apruebe o rechace
    public function getPendientes(): array
    {
        $sql = "
            SELECT s.solicitud_id, s.codigo, s.fecha_solicitud, s.notas, s.usuario_id, s.libro_id,
                   u.nombre_completo, l.titulo
            FROM solicitudes_prestamo s
            INNER JOIN usuarios u ON s.usuario_id = u.usuario_id
            INNER JOIN libros l ON s.libro_id = l.libro_id
            WHERE s.estado = 'pendiente'
            ORDER BY s.fecha_solicitud ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function contarPendientes(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM solicitudes_prestamo WHERE estado = 'pendiente'")->fetchColumn();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM solicitudes_prestamo WHERE solicitud_id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function marcarAprobada(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE solicitudes_prestamo SET estado = 'aprobada' WHERE solicitud_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function marcarRechazada(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE solicitudes_prestamo SET estado = 'rechazada' WHERE solicitud_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function siguienteConsecutivo(): int
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM solicitudes_prestamo')->fetchColumn();
        return $total + 1;
    }
}