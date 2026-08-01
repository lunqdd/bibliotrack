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
}