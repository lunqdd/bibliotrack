<?php

require_once __DIR__ . '/../config/database.php';

class InventarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Existencias agrupadas por libro y ubicación física. "en_estante" cuenta los
    // ejemplares disponibles; los prestados o en reparación no están físicamente ahí
    public function getResumen(): array
    {
        $sql = "
            SELECT
                l.libro_id, u.ubicacion_id, u.pasillo, u.estante,
                l.titulo, a.nombre AS autor, l.isbn,
                COUNT(e.ejemplar_id) AS totales,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS en_estante
            FROM ejemplares e
            INNER JOIN libros l ON e.libro_id = l.libro_id
            INNER JOIN autores a ON l.autor_id = a.autor_id
            INNER JOIN ubicaciones u ON e.ubicacion_id = u.ubicacion_id
            GROUP BY l.libro_id, u.ubicacion_id, u.pasillo, u.estante, l.titulo, a.nombre, l.isbn
            ORDER BY u.pasillo, u.estante, l.titulo
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function contarTotalEjemplares(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ejemplares')->fetchColumn();
    }

    public function contarPorEstado(string $estado): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ejemplares WHERE estado = :estado');
        $stmt->execute([':estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }

    // Mueve la sección (pasillo/estante) y ajusta cuántos ejemplares de ese libro quedan
    // disponibles en estante, sin tocar los que ya están prestados
    public function actualizarSeccion(int $libroId, int $ubicacionId, string $pasillo, string $estante, int $enEstante): bool
    {
        $this->db->prepare('UPDATE ubicaciones SET pasillo = :pasillo, estante = :estante WHERE ubicacion_id = :id')
            ->execute([':pasillo' => $pasillo, ':estante' => $estante, ':id' => $ubicacionId]);

        $stmt = $this->db->prepare(
            "SELECT ejemplar_id, estado FROM ejemplares WHERE libro_id = :libro_id AND ubicacion_id = :ubicacion_id"
        );
        $stmt->execute([':libro_id' => $libroId, ':ubicacion_id' => $ubicacionId]);
        $ejemplares = $stmt->fetchAll();

        $disponibles = array_filter($ejemplares, fn($e) => $e['estado'] === 'disponible');
        $ajustables = array_filter($ejemplares, fn($e) => $e['estado'] !== 'prestado');
        $diferencia = $enEstante - count($disponibles);

        if ($diferencia > 0) {
            $candidatos = array_filter($ajustables, fn($e) => $e['estado'] !== 'disponible');
            foreach (array_slice($candidatos, 0, $diferencia) as $ejemplar) {
                $this->cambiarEstadoEjemplar((int) $ejemplar['ejemplar_id'], 'disponible');
            }
        } elseif ($diferencia < 0) {
            foreach (array_slice(array_values($disponibles), 0, abs($diferencia)) as $ejemplar) {
                $this->cambiarEstadoEjemplar((int) $ejemplar['ejemplar_id'], 'en_reparacion');
            }
        }

        return true;
    }

    private function cambiarEstadoEjemplar(int $ejemplarId, string $estado): void
    {
        $this->db->prepare('UPDATE ejemplares SET estado = :estado WHERE ejemplar_id = :id')
            ->execute([':estado' => $estado, ':id' => $ejemplarId]);
    }
}
