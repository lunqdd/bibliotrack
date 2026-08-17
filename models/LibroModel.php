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

    // Catálogo completo con ejemplares totales/disponibles, para la gestión administrativa
    public function getAllAdmin(): array
    {
        $sql = "
            SELECT
                l.libro_id, l.codigo, l.titulo, l.isbn, l.anio_publicacion, l.portada_url,
                a.nombre AS autor,
                ed.nombre AS editorial,
                g.nombre AS genero,
                COUNT(e.ejemplar_id) AS ejemplares,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS disponibles
            FROM libros l
            INNER JOIN autores a ON l.autor_id = a.autor_id
            LEFT JOIN editoriales ed ON l.editorial_id = ed.editorial_id
            LEFT JOIN generos g ON l.genero_id = g.genero_id
            LEFT JOIN ejemplares e ON e.libro_id = l.libro_id
            GROUP BY l.libro_id, l.codigo, l.titulo, l.isbn, l.anio_publicacion, l.portada_url, a.nombre, ed.nombre, g.nombre
            ORDER BY l.titulo
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getEditoriales(): array
    {
        return $this->db->query('SELECT editorial_id, nombre FROM editoriales ORDER BY nombre')->fetchAll();
    }

    public function getAutores(): array
    {
        return $this->db->query('SELECT autor_id, nombre FROM autores ORDER BY nombre')->fetchAll();
    }

    // Registra un libro nuevo junto con sus primeros ejemplares
    public function create(array $data, int $ejemplaresIniciales): bool
    {
        $autorId = $this->buscarOCrear('autores', 'autor_id', $data['autor']);
        $editorialId = $data['editorial'] !== '' ? $this->buscarOCrear('editoriales', 'editorial_id', $data['editorial']) : null;
        $generoId = $data['genero'] !== '' ? $this->buscarOCrear('generos', 'genero_id', $data['genero']) : null;

        $stmt = $this->db->prepare(
            "INSERT INTO libros (codigo, titulo, isbn, autor_id, editorial_id, genero_id, anio_publicacion, portada_url)
             VALUES (:codigo, :titulo, :isbn, :autor_id, :editorial_id, :genero_id, :anio, :portada_url)"
        );

        $creado = $stmt->execute([
            ':codigo'      => $this->generarCodigo(),
            ':titulo'      => $data['titulo'],
            ':isbn'        => $data['isbn'],
            ':autor_id'    => $autorId,
            ':editorial_id' => $editorialId,
            ':genero_id'   => $generoId,
            ':anio'        => $data['anio'] ?: null,
            ':portada_url' => $data['portada_url'] ?: null,
        ]);

        if (!$creado) {
            return false;
        }

        $libroId = (int) $this->db->lastInsertId();
        $ubicacionId = (int) $this->db->query('SELECT ubicacion_id FROM ubicaciones ORDER BY ubicacion_id LIMIT 1')->fetchColumn();

        $stmtEjemplar = $this->db->prepare(
            "INSERT INTO ejemplares (libro_id, ubicacion_id, estado) VALUES (:libro_id, :ubicacion_id, 'disponible')"
        );
        for ($i = 0; $i < $ejemplaresIniciales; $i++) {
            $stmtEjemplar->execute([':libro_id' => $libroId, ':ubicacion_id' => $ubicacionId]);
        }

        return true;
    }

    // Edita los datos bibliográficos. Los ejemplares no se tocan aquí: se gestionan desde Inventario
    public function update(int $id, array $data): bool
    {
        $autorId = $this->buscarOCrear('autores', 'autor_id', $data['autor']);
        $editorialId = $data['editorial'] !== '' ? $this->buscarOCrear('editoriales', 'editorial_id', $data['editorial']) : null;
        $generoId = $data['genero'] !== '' ? $this->buscarOCrear('generos', 'genero_id', $data['genero']) : null;

        $stmt = $this->db->prepare(
            "UPDATE libros
             SET titulo = :titulo, isbn = :isbn, autor_id = :autor_id, editorial_id = :editorial_id,
                 genero_id = :genero_id, anio_publicacion = :anio, portada_url = :portada_url
             WHERE libro_id = :id"
        );

        return $stmt->execute([
            ':titulo'      => $data['titulo'],
            ':isbn'        => $data['isbn'],
            ':autor_id'    => $autorId,
            ':editorial_id' => $editorialId,
            ':genero_id'   => $generoId,
            ':anio'        => $data['anio'] ?: null,
            ':portada_url' => $data['portada_url'] ?: null,
            ':id'          => $id,
        ]);
    }

    // Elimina el libro y sus ejemplares. Falla si algún ejemplar tiene préstamos asociados (FK)
    public function delete(int $id): bool
    {
        $this->db->prepare('DELETE FROM ejemplares WHERE libro_id = :id')->execute([':id' => $id]);

        $stmt = $this->db->prepare('DELETE FROM libros WHERE libro_id = :id');
        return $stmt->execute([':id' => $id]);
    }

    // Devuelve el id existente por nombre o crea el registro si no existe (autor/editorial/género)
    private function buscarOCrear(string $tabla, string $columnaId, string $nombre): int
    {
        $stmt = $this->db->prepare("SELECT $columnaId FROM $tabla WHERE nombre = :nombre LIMIT 1");
        $stmt->execute([':nombre' => $nombre]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $stmt = $this->db->prepare("INSERT INTO $tabla (nombre) VALUES (:nombre)");
        $stmt->execute([':nombre' => $nombre]);
        return (int) $this->db->lastInsertId();
    }

    private function generarCodigo(): string
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM libros')->fetchColumn();
        return 'LIB-' . str_pad((string) ($total + 1), 3, '0', STR_PAD_LEFT);
    }
}