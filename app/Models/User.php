<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, r.label as role_label 
            FROM `{$this->table}` u
            JOIN `roles` r ON u.role_id = r.id
            WHERE u.email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findWithRole(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, r.label as role_label 
            FROM `{$this->table}` u
            JOIN `roles` r ON u.role_id = r.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllUsersWithRoles(): array
    {
        $stmt = $this->db->query("
            SELECT u.id, u.first_name, u.last_name, u.email, u.is_active, u.created_at,
                   r.id as role_id, r.name as role_name, r.label as role_label
            FROM `{$this->table}` u
            JOIN `roles` r ON u.role_id = r.id
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO `{$this->table}` 
                (first_name, last_name, email, password_hash, role_id, is_active) 
                VALUES (:first_name, :last_name, :email, :password_hash, :role_id, :is_active)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'first_name'    => trim($data['first_name']),
            'last_name'     => trim($data['last_name']),
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role_id'       => $data['role_id'] ?? 3, // Défaut : Membre
            'is_active'     => $data['is_active'] ?? 1
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateRole(int $userId, int $roleId): bool
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET role_id = :role_id WHERE id = :id");
        return $stmt->execute(['role_id' => $roleId, 'id' => $userId]);
    }

    public function toggleActive(int $userId, int $isActive): bool
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET is_active = :is_active WHERE id = :id");
        return $stmt->execute(['is_active' => $isActive, 'id' => $userId]);
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET password_hash = :hash WHERE id = :id");
        return $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }
}
