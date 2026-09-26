<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Task extends Model
{
    protected string $table = 'tasks';

    /**
     * Récupère les tâches avec détails des projets et des assignés multiples
     */
    public function getTasksWithDetails(array $filters = []): array
    {
        $sql = "
            SELECT t.*, 
                   p.name as project_name, p.code_prefix,
                   c.first_name as creator_first_name, c.last_name as creator_last_name,
                   GROUP_CONCAT(CONCAT(u.id, ':', u.first_name, ' ', u.last_name) SEPARATOR '||') as assignees_data
            FROM `{$this->table}` t
            JOIN `projects` p ON t.project_id = p.id
            JOIN `users` c ON t.creator_id = c.id
            LEFT JOIN `task_assignments` ta ON t.id = ta.task_id
            LEFT JOIN `users` u ON ta.user_id = u.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['project_id'])) {
            $sql .= " AND t.project_id = :project_id";
            $params['project_id'] = (int)$filters['project_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['assigned_user_id'])) {
            $sql .= " AND t.id IN (SELECT task_id FROM `task_assignments` WHERE user_id = :assigned_user_id)";
            $params['assigned_user_id'] = (int)$filters['assigned_user_id'];
        }

        $sql .= " GROUP BY t.id ORDER BY t.due_date ASC, t.priority DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();

        // Parser assignees_data en tableau structuré
        foreach ($tasks as &$task) {
            $task['assignees'] = [];
            if (!empty($task['assignees_data'])) {
                $items = explode('||', $task['assignees_data']);
                foreach ($items as $item) {
                    if (str_contains($item, ':')) {
                        [$uid, $name] = explode(':', $item, 2);
                        $task['assignees'][] = ['id' => (int)$uid, 'name' => $name];
                    }
                }
            }
            unset($task['assignees_data']);

            // Marqueur de retard calculé
            $task['is_overdue'] = !empty($task['due_date']) && 
                                  $task['due_date'] < date('Y-m-d') && 
                                  $task['status'] !== 'terminee';
        }

        return $tasks;
    }

    public function findWithAssignees(int $taskId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, p.name as project_name, p.code_prefix,
                   c.first_name as creator_first_name, c.last_name as creator_last_name
            FROM `{$this->table}` t
            JOIN `projects` p ON t.project_id = p.id
            JOIN `users` c ON t.creator_id = c.id
            WHERE t.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $taskId]);
        $task = $stmt->fetch();

        if (!$task) {
            return null;
        }

        // Récupérer les assignés
        $stmtAssignees = $this->db->prepare("
            SELECT u.id, u.first_name, u.last_name, u.email
            FROM `task_assignments` ta
            JOIN `users` u ON ta.user_id = u.id
            WHERE ta.task_id = :task_id
        ");
        $stmtAssignees->execute(['task_id' => $taskId]);
        $task['assignees'] = $stmtAssignees->fetchAll();

        $task['is_overdue'] = !empty($task['due_date']) && 
                              $task['due_date'] < date('Y-m-d') && 
                              $task['status'] !== 'terminee';

        return $task;
    }

    public function createTask(array $data, array $assigneeIds = []): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO `{$this->table}` 
                (project_id, creator_id, title, description, priority, status, due_date, estimated_hours, blocked_reason)
                VALUES 
                (:project_id, :creator_id, :title, :description, :priority, :status, :due_date, :estimated_hours, :blocked_reason)
            ");

            $stmt->execute([
                'project_id'      => (int)$data['project_id'],
                'creator_id'      => (int)$data['creator_id'],
                'title'           => trim($data['title']),
                'description'     => trim($data['description'] ?? ''),
                'priority'        => $data['priority'] ?? 'moyenne',
                'status'          => $data['status'] ?? 'a_faire',
                'due_date'        => !empty($data['due_date']) ? $data['due_date'] : null,
                'estimated_hours' => !empty($data['estimated_hours']) ? (float)$data['estimated_hours'] : 0.0,
                'blocked_reason'  => ($data['status'] === 'bloquee') ? trim($data['blocked_reason'] ?? '') : null
            ]);

            $taskId = (int)$this->db->lastInsertId();

            // Enregistrer l'attribution multiple
            if (!empty($assigneeIds)) {
                $stmtAssign = $this->db->prepare("
                    INSERT IGNORE INTO `task_assignments` (task_id, user_id) 
                    VALUES (:task_id, :user_id)
                ");
                foreach ($assigneeIds as $uid) {
                    $stmtAssign->execute(['task_id' => $taskId, 'user_id' => (int)$uid]);
                }
            }

            $this->db->commit();
            return $taskId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $taskId, string $status, ?string $blockedReason = null): bool
    {
        $allowed = ['a_faire', 'en_cours', 'en_revue', 'terminee', 'bloquee'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE `{$this->table}` 
            SET status = :status, 
                blocked_reason = :blocked_reason,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'status'         => $status,
            'blocked_reason' => ($status === 'bloquee') ? $blockedReason : null,
            'id'             => $taskId
        ]);
    }
}
