<?php

require_once __DIR__ . '/../config/database.php';

class LibroModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Géneros disponibles para el filtro del catálogo
    public function getGeneros(): array
    {
        $stmt = $this->db->query('SELECT genero_id, nombre FROM generos ORDER BY nombre');
        return $stmt->fetchAll();
    }

    // Catálogo completo con disponibilidad, filtrable por texto y género
    public function getCatalogo(string $busqueda = '', ?int $generoId = null): array
    {
        $sql = "
            SELECT
                l.libro_id,
                l.titulo,
                l.portada_url,
                a.nombre AS autor,
                g.nombre AS genero,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS disponibles
            FROM libros l
            INNER JOIN autores a ON l.autor_id = a.autor_id
            LEFT JOIN generos g ON l.genero_id = g.genero_id
            LEFT JOIN ejemplares e ON e.libro_id = l.libro_id
            WHERE (l.titulo LIKE :busqueda1 OR a.nombre LIKE :busqueda2)
        ";

        $params = [
            ':busqueda1' => '%' . $busqueda . '%',
            ':busqueda2' => '%' . $busqueda . '%',
        ];

        if ($generoId) {
            $sql .= ' AND l.genero_id = :genero_id';
            $params[':genero_id'] = $generoId;
        }

        $sql .= ' GROUP BY l.libro_id, l.titulo, l.portada_url, a.nombre, g.nombre ORDER BY l.titulo';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Libros con al menos un ejemplar disponible (para el select de solicitud)
    public function getDisponibles(): array
    {
        $sql = "
            SELECT l.libro_id, l.titulo
            FROM libros l
            INNER JOIN ejemplares e ON e.libro_id = l.libro_id
            WHERE e.estado = 'disponible'
            GROUP BY l.libro_id, l.titulo
            ORDER BY l.titulo
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Sugerencias para el lector: libros que no tiene en préstamo actualmente
    public function getRecomendados(int $usuarioId, int $limite = 3): array
    {
        $sql = "
            SELECT l.libro_id, l.titulo, a.nombre AS autor
            FROM libros l
            INNER JOIN autores a ON l.autor_id = a.autor_id
            WHERE l.libro_id NOT IN (
                SELECT e.libro_id
                FROM prestamos p
                INNER JOIN ejemplares e ON p.ejemplar_id = e.ejemplar_id
                LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
                WHERE p.usuario_id = :usuario_id AND d.devolucion_id IS NULL
            )
            ORDER BY RAND()
            LIMIT " . (int) $limite . "
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }
}