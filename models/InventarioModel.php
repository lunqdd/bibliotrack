<?php

require_once __DIR__ . '/../config/database.php';

class InventarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Indicadores generales de existencias mostrados en las tarjetas superiores
    public function obtenerStats(): array
    {
        return [
            'total'        => (int) $this->db->query('SELECT COUNT(*) FROM ejemplares')->fetchColumn(),
            'disponibles'  => (int) $this->db->query("SELECT COUNT(*) FROM ejemplares WHERE estado = 'disponible'")->fetchColumn(),
            'prestados'    => (int) $this->db->query("SELECT COUNT(*) FROM ejemplares WHERE estado = 'prestado'")->fetchColumn(),
            'enReparacion' => (int) $this->db->query("SELECT COUNT(*) FROM ejemplares WHERE estado = 'en_reparacion'")->fetchColumn(),
        ];
    }

    // Existencias agrupadas por libro y ubicación física, filtrable por texto y estado de sección
    public function getListado(string $busqueda = '', string $estado = ''): array
    {
        $sql = "
            SELECT
                l.libro_id,
                u.pasillo,
                u.estante,
                l.titulo,
                a.nombre AS autor,
                l.isbn,
                COUNT(*) AS total,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS en_estante
            FROM ejemplares e
            INNER JOIN libros l ON l.libro_id = e.libro_id
            INNER JOIN autores a ON a.autor_id = l.autor_id
            INNER JOIN ubicaciones u ON u.ubicacion_id = e.ubicacion_id
            WHERE (u.pasillo LIKE :busqueda1 OR u.estante LIKE :busqueda2 OR l.titulo LIKE :busqueda3)
            GROUP BY l.libro_id, u.pasillo, u.estante, l.titulo, a.nombre, l.isbn
            ORDER BY u.pasillo, u.estante
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':busqueda1' => '%' . $busqueda . '%',
            ':busqueda2' => '%' . $busqueda . '%',
            ':busqueda3' => '%' . $busqueda . '%',
        ]);
        $filas = $stmt->fetchAll();

        foreach ($filas as &$fila) {
            $fila['total'] = (int) $fila['total'];
            $fila['en_estante'] = (int) $fila['en_estante'];
            $fila['estado'] = $this->calcularEstado($fila['en_estante'], $fila['total']);
        }
        unset($fila);

        if ($estado !== '') {
            $filas = array_values(array_filter($filas, fn($fila) => $fila['estado'] === $estado));
        }

        return $filas;
    }

    // Datos de una fila para precargar el modal de edición
    public function getByLibroId(int $libroId): array|false
    {
        $sql = "
            SELECT
                l.libro_id, l.titulo, l.isbn, a.nombre AS autor,
                u.pasillo, u.estante,
                COUNT(*) AS total,
                COUNT(CASE WHEN e.estado = 'disponible' THEN 1 END) AS en_estante
            FROM ejemplares e
            INNER JOIN libros l ON l.libro_id = e.libro_id
            INNER JOIN autores a ON a.autor_id = l.autor_id
            INNER JOIN ubicaciones u ON u.ubicacion_id = e.ubicacion_id
            WHERE l.libro_id = :libro_id
            GROUP BY l.libro_id, l.titulo, l.isbn, a.nombre, u.pasillo, u.estante
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':libro_id' => $libroId]);
        $fila = $stmt->fetch();

        if (!$fila) {
            return false;
        }

        $fila['total'] = (int) $fila['total'];
        $fila['en_estante'] = (int) $fila['en_estante'];

        return $fila;
    }

    // Reubica las copias del libro y ajusta cuántas quedan disponibles en el estante.
    // Las copias ya prestadas no se tocan: el conteo de estantería solo se reparte
    // entre disponibles y en reparación. Devuelve false si el conteo pedido no cabe.
    public function actualizarUbicacion(int $libroId, string $pasillo, string $estante, int $enEstante): bool
    {
        $stmt = $this->db->prepare('SELECT ejemplar_id, estado FROM ejemplares WHERE libro_id = :libro_id');
        $stmt->execute([':libro_id' => $libroId]);
        $ejemplares = $stmt->fetchAll();

        $enEstanteria = array_values(array_filter($ejemplares, fn($e) => $e['estado'] !== 'prestado'));

        if ($enEstante > count($enEstanteria)) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $ubicacionId = $this->obtenerOcrearUbicacion($pasillo, $estante);

            $stmtUbicacion = $this->db->prepare('UPDATE ejemplares SET ubicacion_id = :ubicacion_id WHERE libro_id = :libro_id');
            $stmtUbicacion->execute([':ubicacion_id' => $ubicacionId, ':libro_id' => $libroId]);

            $stmtEstado = $this->db->prepare('UPDATE ejemplares SET estado = :estado WHERE ejemplar_id = :id');
            foreach ($enEstanteria as $indice => $ejemplar) {
                $estado = $indice < $enEstante ? 'disponible' : 'en_reparacion';
                $stmtEstado->execute([':estado' => $estado, ':id' => $ejemplar['ejemplar_id']]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Completo / Parcial / Agotado según cuántas copias siguen físicamente en el estante
    private function calcularEstado(int $enEstante, int $total): string
    {
        if ($enEstante <= 0) return 'Agotado';
        if ($enEstante < $total) return 'Parcial';
        return 'Completo';
    }

    private function obtenerOcrearUbicacion(string $pasillo, string $estante): int
    {
        $stmt = $this->db->prepare('SELECT ubicacion_id FROM ubicaciones WHERE pasillo = :pasillo AND estante = :estante');
        $stmt->execute([':pasillo' => $pasillo, ':estante' => $estante]);
        $ubicacionId = $stmt->fetchColumn();

        if ($ubicacionId) {
            return (int) $ubicacionId;
        }

        $stmt = $this->db->prepare('INSERT INTO ubicaciones (pasillo, estante) VALUES (:pasillo, :estante)');
        $stmt->execute([':pasillo' => $pasillo, ':estante' => $estante]);
        return (int) $this->db->lastInsertId();
    }
}
