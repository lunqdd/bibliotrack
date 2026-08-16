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

    public function getAdminListado(string $busqueda = '', string $rol = '', string $estado = ''): array
    {
        $sql = "
            SELECT usuario_id, codigo, nombre_completo, identificacion, correo, telefono, direccion, rol, estado, fecha_registro
            FROM usuarios
            WHERE (nombre_completo LIKE :busqueda1 OR correo LIKE :busqueda2 OR identificacion LIKE :busqueda3)
        ";

        $params = [
            ':busqueda1' => '%' . $busqueda . '%',
            ':busqueda2' => '%' . $busqueda . '%',
            ':busqueda3' => '%' . $busqueda . '%',
        ];

        if ($rol !== '') {
            $sql .= ' AND rol = :rol';
            $params[':rol'] = $rol;
        }

        if ($estado !== '') {
            $sql .= ' AND estado = :estado';
            $params[':estado'] = $estado;
        }

        $sql .= ' ORDER BY nombre_completo';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll();

        foreach ($usuarios as &$usuario) {
            $usuario['iniciales'] = $this->iniciales($usuario['nombre_completo']);
            $usuario['color'] = $this->colorAvatar((int) $usuario['usuario_id']);
            $usuario['fecha_registro_texto'] = 'Registrado el ' . (new DateTime($usuario['fecha_registro']))->format('d M, Y');
        }
        unset($usuario);

        return $usuarios;
    }

    public function obtenerStats(): array
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        $activos = (int) $this->db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'activo'")->fetchColumn();
        $conPrestamos = (int) $this->db->query(
            "SELECT COUNT(DISTINCT p.usuario_id)
             FROM prestamos p
             LEFT JOIN devoluciones d ON d.prestamo_id = p.prestamo_id
             WHERE d.devolucion_id IS NULL"
        )->fetchColumn();
        $nuevosEsteMes = (int) $this->db->query(
            "SELECT COUNT(*) FROM usuarios
             WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())"
        )->fetchColumn();

        return [
            'total'         => $total,
            'activos'       => $activos,
            'conPrestamos'  => $conPrestamos,
            'nuevosEsteMes' => $nuevosEsteMes,
        ];
    }

    public function create(array $data): int
    {
        $codigo       = $this->siguienteCodigo();
        $passwordHash = password_hash($data['identificacion'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (codigo, nombre_completo, identificacion, correo, telefono, direccion, password_hash, rol, estado)
             VALUES (:codigo, :nombre_completo, :identificacion, :correo, :telefono, :direccion, :password_hash, :rol, "activo")'
        );
        $stmt->execute([
            ':codigo'          => $codigo,
            ':nombre_completo' => $data['nombre_completo'],
            ':identificacion'  => $data['identificacion'],
            ':correo'          => $data['correo'],
            ':telefono'        => $data['telefono'] !== '' ? $data['telefono'] : null,
            ':direccion'       => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':password_hash'   => $passwordHash,
            ':rol'             => $data['rol'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET nombre_completo = :nombre_completo, identificacion = :identificacion, correo = :correo,
                 telefono = :telefono, direccion = :direccion, rol = :rol
             WHERE usuario_id = :id'
        );
        $stmt->execute([
            ':nombre_completo' => $data['nombre_completo'],
            ':identificacion'  => $data['identificacion'],
            ':correo'          => $data['correo'],
            ':telefono'        => $data['telefono'] !== '' ? $data['telefono'] : null,
            ':direccion'       => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':rol'             => $data['rol'],
            ':id'              => $id,
        ]);
    }

    private function siguienteCodigo(): string
    {
        $total = (int) $this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        return 'USR-' . str_pad((string) ($total + 1), 3, '0', STR_PAD_LEFT);
    }

    private function iniciales(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre));
        $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1));
        if (isset($partes[1])) {
            $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));
        }
        return $iniciales;
    }

    private function colorAvatar(int $id): string
    {
        $paleta = ['#4b3621', '#725a42', '#59422c', '#354a52', '#5b3a5c', '#3f5d4f'];
        return $paleta[$id % count($paleta)];
    }
    
}