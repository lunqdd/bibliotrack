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

    // Editoriales disponibles para el filtro y el formulario de registro
    public function getEditoriales(): array
    {
        $stmt = $this->db->query('SELECT editorial_id, nombre FROM editoriales ORDER BY nombre');
        return $stmt->fetchAll();
    }

    // Indicadores generales del catálogo mostrados en las tarjetas superiores
    public function obtenerStats(): array
    {
        return [
            'titulos'      => (int) $this->db->query('SELECT COUNT(*) FROM libros')->fetchColumn(),
            'ejemplares'   => (int) $this->db->query('SELECT COUNT(*) FROM ejemplares')->fetchColumn(),
            'disponibles'  => (int) $this->db->query("SELECT COUNT(*) FROM ejemplares WHERE estado = 'disponible'")->fetchColumn(),
            'enReparacion' => (int) $this->db->query("SELECT COUNT(*) FROM ejemplares WHERE estado = 'en_reparacion'")->fetchColumn(),
        ];
    }

    // Listado administrable con existencias, filtrable por texto, género, editorial y disponibilidad
    public function getAdminListado(string $busqueda = '', ?int $generoId = null, ?int $editorialId = null, string $estadoDisponibilidad = ''): array
    {
        $sql = "
            SELECT
                l.libro_id,
                l.codigo,
                l.titulo,
                l.isbn,
                l.portada_url,
                a.nombre AS autor,
                g.nombre AS genero,
                ed.nombre AS editorial,
                COUNT(e.ejemplar_id) AS ejemplares,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS disponibles
            FROM libros l
            INNER JOIN autores a ON l.autor_id = a.autor_id
            LEFT JOIN generos g ON l.genero_id = g.genero_id
            LEFT JOIN editoriales ed ON l.editorial_id = ed.editorial_id
            LEFT JOIN ejemplares e ON e.libro_id = l.libro_id
            WHERE (l.titulo LIKE :busqueda1 OR a.nombre LIKE :busqueda2 OR l.isbn LIKE :busqueda3)
        ";

        $params = [
            ':busqueda1' => '%' . $busqueda . '%',
            ':busqueda2' => '%' . $busqueda . '%',
            ':busqueda3' => '%' . $busqueda . '%',
        ];

        if ($generoId) {
            $sql .= ' AND l.genero_id = :genero_id';
            $params[':genero_id'] = $generoId;
        }

        if ($editorialId) {
            $sql .= ' AND l.editorial_id = :editorial_id';
            $params[':editorial_id'] = $editorialId;
        }

        $sql .= ' GROUP BY l.libro_id, l.codigo, l.titulo, l.isbn, l.portada_url, a.nombre, g.nombre, ed.nombre ORDER BY l.titulo';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $libros = $stmt->fetchAll();

        foreach ($libros as &$libro) {
            $libro['ejemplares'] = (int) $libro['ejemplares'];
            $libro['disponibles'] = (int) $libro['disponibles'];
            $libro['disponibilidad'] = $this->calcularDisponibilidad($libro['disponibles'], $libro['ejemplares']);
        }
        unset($libro);

        if ($estadoDisponibilidad !== '') {
            $libros = array_values(array_filter($libros, fn($libro) => $libro['disponibilidad'] === $estadoDisponibilidad));
        }

        return $libros;
    }

    // Ficha completa de un libro para los modales de ver detalle y editar
    public function getById(int $id): array|false
    {
        $sql = "
            SELECT
                l.libro_id, l.codigo, l.titulo, l.isbn, l.autor_id, l.editorial_id, l.genero_id,
                l.anio_publicacion, l.portada_url,
                a.nombre AS autor, g.nombre AS genero, ed.nombre AS editorial,
                COUNT(e.ejemplar_id) AS ejemplares,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS disponibles
            FROM libros l
            INNER JOIN autores a ON l.autor_id = a.autor_id
            LEFT JOIN generos g ON l.genero_id = g.genero_id
            LEFT JOIN editoriales ed ON l.editorial_id = ed.editorial_id
            LEFT JOIN ejemplares e ON e.libro_id = l.libro_id
            WHERE l.libro_id = :id
            GROUP BY l.libro_id, l.codigo, l.titulo, l.isbn, l.autor_id, l.editorial_id, l.genero_id, l.anio_publicacion, l.portada_url, a.nombre, g.nombre, ed.nombre
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $libro = $stmt->fetch();

        if (!$libro) {
            return false;
        }

        $libro['ejemplares'] = (int) $libro['ejemplares'];
        $libro['disponibles'] = (int) $libro['disponibles'];
        $libro['disponibilidad'] = $this->calcularDisponibilidad($libro['disponibles'], $libro['ejemplares']);

        return $libro;
    }

    // Registra un libro nuevo junto con sus primeras copias físicas
    public function create(array $data, int $ejemplaresIniciales): void
    {
        $this->db->beginTransaction();
        try {
            $autorId = $this->obtenerOcrearAutor($data['autor']);
            $codigo = $this->siguienteCodigo();

            $stmt = $this->db->prepare(
                'INSERT INTO libros (codigo, titulo, isbn, autor_id, editorial_id, genero_id, anio_publicacion, portada_url)
                 VALUES (:codigo, :titulo, :isbn, :autor_id, :editorial_id, :genero_id, :anio, :portada)'
            );
            $stmt->execute([
                ':codigo'       => $codigo,
                ':titulo'       => $data['titulo'],
                ':isbn'         => $data['isbn'],
                ':autor_id'     => $autorId,
                ':editorial_id' => $data['editorial_id'],
                ':genero_id'    => $data['genero_id'],
                ':anio'         => $data['anio_publicacion'],
                ':portada'      => $data['portada_url'] !== '' ? $data['portada_url'] : null,
            ]);

            $libroId = (int) $this->db->lastInsertId();
            $ubicacionId = $this->obtenerUbicacionPorDefecto();

            $stmtEjemplar = $this->db->prepare(
                "INSERT INTO ejemplares (libro_id, ubicacion_id, estado) VALUES (:libro_id, :ubicacion_id, 'disponible')"
            );
            for ($i = 0; $i < $ejemplaresIniciales; $i++) {
                $stmtEjemplar->execute([':libro_id' => $libroId, ':ubicacion_id' => $ubicacionId]);
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Actualiza los datos bibliográficos. Los ejemplares se administran desde Inventario
    public function update(int $id, array $data): void
    {
        $autorId = $this->obtenerOcrearAutor($data['autor']);

        $stmt = $this->db->prepare(
            'UPDATE libros
             SET titulo = :titulo, isbn = :isbn, autor_id = :autor_id, editorial_id = :editorial_id,
                 genero_id = :genero_id, anio_publicacion = :anio, portada_url = :portada
             WHERE libro_id = :id'
        );
        $stmt->execute([
            ':titulo'       => $data['titulo'],
            ':isbn'         => $data['isbn'],
            ':autor_id'     => $autorId,
            ':editorial_id' => $data['editorial_id'],
            ':genero_id'    => $data['genero_id'],
            ':anio'         => $data['anio_publicacion'],
            ':portada'      => $data['portada_url'] !== '' ? $data['portada_url'] : null,
            ':id'           => $id,
        ]);
    }

    // Elimina el libro. La base de datos rechaza el borrado si aún tiene ejemplares registrados
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM libros WHERE libro_id = :id');
        $stmt->execute([':id' => $id]);
    }

    // AGOTADO / PARCIAL / DISPONIBLE, la misma regla que usaba el catálogo de prueba
    private function calcularDisponibilidad(int $disponibles, int $ejemplares): string
    {
        if ($disponibles <= 0) return 'AGOTADO';
        if ($disponibles < $ejemplares) return 'PARCIAL';
        return 'DISPONIBLE';
    }

    private function siguienteCodigo(): string
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM libros')->fetchColumn();
        return 'LIB-' . str_pad((string) ($total + 1), 3, '0', STR_PAD_LEFT);
    }

    // Reutiliza el autor si ya existe en el catálogo; si no, lo crea
    private function obtenerOcrearAutor(string $nombre): int
    {
        $stmt = $this->db->prepare('SELECT autor_id FROM autores WHERE nombre = :nombre');
        $stmt->execute([':nombre' => $nombre]);
        $autorId = $stmt->fetchColumn();

        if ($autorId) {
            return (int) $autorId;
        }

        $stmt = $this->db->prepare('INSERT INTO autores (nombre) VALUES (:nombre)');
        $stmt->execute([':nombre' => $nombre]);
        return (int) $this->db->lastInsertId();
    }

    // Ubicación donde se colocan las copias nuevas hasta que Inventario les asigne un lugar definitivo
    private function obtenerUbicacionPorDefecto(): int
    {
        $ubicacionId = $this->db->query('SELECT ubicacion_id FROM ubicaciones ORDER BY ubicacion_id LIMIT 1')->fetchColumn();
        if ($ubicacionId) {
            return (int) $ubicacionId;
        }

        $stmt = $this->db->prepare("INSERT INTO ubicaciones (pasillo, estante) VALUES ('A', '1')");
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }
}