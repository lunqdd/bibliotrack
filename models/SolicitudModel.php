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

    private function siguienteConsecutivo(): int
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM solicitudes_prestamo')->fetchColumn();
        return $total + 1;
    }
}