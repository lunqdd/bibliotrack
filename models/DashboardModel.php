<?php

require_once __DIR__ . '/../config/database.php';

class DashboardModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Total de libros registrados
    public function obtenerTotalLibros()
    {
        $sql = "
            SELECT COUNT(*)
            FROM libros
        ";
        return $this->db->query($sql)->fetchColumn();
    }

    // Cantidad total de ejemplares físicos
    public function obtenerTotalEjemplares()
    {
        $sql = "
            SELECT COUNT(*)
            FROM ejemplares
        ";
        return $this->db->query($sql)->fetchColumn();
    }

    // Préstamos que todavía no tienen devolución
    public function obtenerPrestamosActivos()
    {
        $sql = "
            SELECT COUNT(*)
            FROM prestamos p
            LEFT JOIN devoluciones d
                ON p.prestamo_id = d.prestamo_id
            WHERE d.devolucion_id IS NULL
        ";
        return $this->db->query($sql)->fetchColumn();
    }

    // Préstamos vencidos
    public function obtenerDevolucionesTardias()
    {
        $sql = "
            SELECT COUNT(*)
            FROM prestamos p
            LEFT JOIN devoluciones d
                ON p.prestamo_id = d.prestamo_id
            WHERE d.devolucion_id IS NULL
            AND p.fecha_devolucion_programada < CURDATE()
        ";
        return $this->db->query($sql)->fetchColumn();
    }

    // Usuarios activos
    public function obtenerUsuariosRegistrados()
    {
        $sql = "
            SELECT COUNT(*)
            FROM usuarios
            WHERE estado='activo'
        ";
        return $this->db->query($sql)->fetchColumn();
    }

    // Actividad reciente
    public function obtenerActividadReciente()
    {
        $sql = "
            SELECT
                u.nombre_completo AS usuario,
                l.titulo AS libro,
                'Préstamo' AS accion,
                p.fecha_creacion AS fecha
            FROM prestamos p
            INNER JOIN usuarios u
                ON p.usuario_id = u.usuario_id
            INNER JOIN ejemplares e
                ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l
                ON e.libro_id = l.libro_id
            UNION ALL
            SELECT
                u.nombre_completo,
                l.titulo,
                'Devolución',
                d.fecha_devolucion
            FROM devoluciones d
            INNER JOIN prestamos p
                ON d.prestamo_id = p.prestamo_id
            INNER JOIN usuarios u
                ON p.usuario_id = u.usuario_id
            INNER JOIN ejemplares e
                ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l
                ON e.libro_id = l.libro_id
            ORDER BY fecha DESC
            LIMIT 5
        ";
        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    // Libros más prestados
    public function obtenerLibrosPopulares()
    {
        $sql = "
            SELECT
                l.titulo,
                a.nombre AS autor,
                COUNT(p.prestamo_id) AS prestamos
            FROM prestamos p
            INNER JOIN ejemplares e
                ON p.ejemplar_id = e.ejemplar_id
            INNER JOIN libros l
                ON e.libro_id = l.libro_id
            INNER JOIN autores a
                ON l.autor_id = a.autor_id
            GROUP BY
                l.libro_id,
                l.titulo,
                a.nombre
            ORDER BY prestamos DESC
            LIMIT 3

        ";
        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tendencia semanal de préstamos
    public function obtenerTendenciaSemanal()
    {
        $sql = "
            SELECT
                DAYNAME(fecha_prestamo) AS dia,
                COUNT(*) AS cantidad

            FROM prestamos
            WHERE fecha_prestamo >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DAYNAME(fecha_prestamo)

        ";

        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}