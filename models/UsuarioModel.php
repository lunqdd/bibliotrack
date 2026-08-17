<?php

require_once __DIR__ . '/../config/database.php';

class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    public function getByCorreo(string $correo): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM usuarios
             WHERE correo = :correo
             LIMIT 1"
        );

        $stmt->execute([
            ':correo' => $correo
        ]);

        return $stmt->fetch();
    }

    public function actualizarUltimoAcceso(int $usuarioId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
         SET ultimo_acceso = NOW()
         WHERE usuario_id = :usuario_id"
        );

        return $stmt->execute([
            ':usuario_id' => $usuarioId
        ]);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM usuarios
             WHERE usuario_id = :id
             LIMIT 1"
        );

        $stmt->execute([':id' => $id]);

        return $stmt->fetch();
    }

    public function actualizarPerfil(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET nombre_completo = :nombre_completo,
                 correo = :correo,
                 telefono = :telefono,
                 direccion = :direccion
             WHERE usuario_id = :id"
        );

        return $stmt->execute([
            ':nombre_completo' => $data['nombre_completo'],
            ':correo'          => $data['correo'],
            ':telefono'        => $data['telefono'],
            ':direccion'       => $data['direccion'],
            ':id'              => $id,
        ]);
    }

    public function actualizarClave(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET password_hash = :password_hash
             WHERE usuario_id = :id"
        );

        return $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id'            => $id,
        ]);
    }

    // Listado completo para la gestión administrativa de usuarios
    public function getAll(): array
    {
        $sql = "
            SELECT usuario_id, codigo, nombre_completo, identificacion, correo, telefono,
                   direccion, rol, estado, fecha_registro
            FROM usuarios
            ORDER BY fecha_registro DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Registra un usuario nuevo desde el panel de administración
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (codigo, nombre_completo, identificacion, correo, telefono, direccion, password_hash, rol, estado)
             VALUES (:codigo, :nombre_completo, :identificacion, :correo, :telefono, :direccion, :password_hash, :rol, 'activo')"
        );

        return $stmt->execute([
            ':codigo'          => $this->generarCodigo(),
            ':nombre_completo' => $data['nombre_completo'],
            ':identificacion'  => $data['identificacion'],
            ':correo'          => $data['correo'],
            ':telefono'        => $data['telefono'],
            ':direccion'       => $data['direccion'],
            ':password_hash'   => password_hash($data['password'], PASSWORD_DEFAULT),
            ':rol'             => $data['rol'],
        ]);
    }

    // Edición administrativa: incluye rol y estado, a diferencia de actualizarPerfil()
    public function actualizar(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET nombre_completo = :nombre_completo,
                 identificacion = :identificacion,
                 correo = :correo,
                 telefono = :telefono,
                 direccion = :direccion,
                 rol = :rol,
                 estado = :estado
             WHERE usuario_id = :id"
        );

        return $stmt->execute([
            ':nombre_completo' => $data['nombre_completo'],
            ':identificacion'  => $data['identificacion'],
            ':correo'          => $data['correo'],
            ':telefono'        => $data['telefono'],
            ':direccion'       => $data['direccion'],
            ':rol'             => $data['rol'],
            ':estado'          => $data['estado'],
            ':id'              => $id,
        ]);
    }

    public function contarActivos(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'activo'")->fetchColumn();
    }

    // Usuarios con al menos un préstamo activo (sin devolución)
    public function contarConPrestamosActivos(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT p.usuario_id)
            FROM prestamos p
            LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
            WHERE d.devolucion_id IS NULL
        ";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    public function contarNuevosEsteMes(): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM usuarios
            WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())
        ";
        return (int) $this->db->query($sql)->fetchColumn();
    }

    // El código de socio es solo un identificador de exhibición; no participa en el login
    private function generarCodigo(): string
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        return 'ID-' . str_pad((string) ($total + 1), 6, '0', STR_PAD_LEFT);
    }
}