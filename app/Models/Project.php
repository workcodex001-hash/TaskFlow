<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Project extends Model
{
    protected string $table = 'projects';

    /**
     * Récupère les projets visibles pour un utilisateur donné
     * Les administrateurs voient tous les projets ; les autres uniquement les projets dont ils sont membres ou propriétaires
     */
    public function getProjectsForUser(int $userId, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $sql = "
                SELECT p.*, 
                       u.first_name as owner_first_name, u.last_name as owner_last_name,
                       COUNT(DISTINCT t.id) as total_tasks,
                       COUNT(DISTINCT CASE WHEN t.status = 'terminee' THEN t.id END) as done_tasks,
                       COUNT(DISTINCT pm.user_id) as total_members
                FROM `{$this->table}` p
                JOIN `users` u ON p.owner_id = u.id
                LEFT JOIN `tasks` t ON p.id = t.project_id
                LEFT JOIN `project_members` pm ON p.id = pm.project_id
                GROUP BY p.id
                ORDER BY p.id DESC
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT p.*, 
                   u.first_name as owner_first_name, u.last_name as owner_last_name,
                   COUNT(DISTINCT t.id) as total_tasks,
                   COUNT(DISTINCT CASE WHEN t.status = 'terminee' THEN t.id END) as done_tasks,
                   COUNT(DISTINCT pm2.user_id) as total_members
            FROM `{$this->table}` p
            JOIN `users` u ON p.owner_id = u.id
            LEFT JOIN `project_members` pm ON p.id = pm.project_id AND pm.user_id = :user_id
            LEFT JOIN `project_members` pm2 ON p.id = pm2.project_id
            LEFT JOIN `tasks` t ON p.id = t.project_id
            WHERE p.owner_id = :user_id2 OR pm.user_id IS NOT NULL
            GROUP BY p.id
            ORDER BY p.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'  => $userId,
            'user_id2' => $userId
        ]);
        return $stmt->fetchAll();
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.first_name as owner_first_name, u.last_name as owner_last_name, u.email as owner_email
            FROM `{$this->table}` p
            JOIN `users` u ON p.owner_id = u.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();
        return $project ?: null;
    }

    public function createProject(array $data, array $memberIds = []): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO `{$this->table}` (name, code_prefix, description, owner_id, status, start_date, due_date)
                VALUES (:name, :code_prefix, :description, :owner_id, :status, :start_date, :due_date)
            ");
            $stmt->execute([
                'name'        => trim($data['name']),
                'code_prefix' => strtoupper(trim($data['code_prefix'])),
                'description' => trim($data['description'] ?? ''),
                'owner_id'    => $data['owner_id'],
                'status'      => $data['status'] ?? 'actif',
                'start_date'  => !empty($data['start_date']) ? $data['start_date'] : null,
                'due_date'    => !empty($data['due_date']) ? $data['due_date'] : null
            ]);

            $projectId = (int)$this->db->lastInsertId();

            // Inclure automatiquement le chef de projet dans les membres
            if (!in_array($data['owner_id'], $memberIds, true)) {
                $memberIds[] = $data['owner_id'];
            }

            // Associer les membres
            if (!empty($memberIds)) {
                $stmtMember = $this->db->prepare("
                    INSERT IGNORE INTO `project_members` (project_id, user_id) 
                    VALUES (:project_id, :user_id)
                ");
                foreach ($memberIds as $uid) {
                    $stmtMember->execute(['project_id' => $projectId, 'user_id' => (int)$uid]);
                }
            }

            $this->db->commit();
            return $projectId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getMembers(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.first_name, u.last_name, u.email, u.role_id, r.label as role_label, pm.joined_at
            FROM `project_members` pm
            JOIN `users` u ON pm.user_id = u.id
            JOIN `roles` r ON u.role_id = r.id
            WHERE pm.project_id = :project_id
            ORDER BY u.last_name ASC
        ");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    public function isMember(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM `project_members` 
            WHERE project_id = :project_id AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['project_id' => $projectId, 'user_id' => $userId]);
        return (bool)$stmt->fetchColumn();
    }
}
