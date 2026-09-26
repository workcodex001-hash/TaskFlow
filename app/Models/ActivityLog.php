<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    public function log(string $action, ?int $userId = null, ?int $projectId = null, ?int $taskId = null, ?array $details = null, ?string $ip = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO `{$this->table}` (user_id, project_id, task_id, action, details, ip_address)
            VALUES (:user_id, :project_id, :task_id, :action, :details, :ip_address)
        ");

        return $stmt->execute([
            'user_id'    => $userId,
            'project_id' => $projectId,
            'task_id'    => $taskId,
            'action'     => $action,
            'details'    => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
        ]);
    }

    public function getLatest(int $limit = 25): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, u.first_name, u.last_name, u.email
            FROM `{$this->table}` l
            LEFT JOIN `users` u ON l.user_id = u.id
            ORDER BY l.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recherche filtrée dans les logs d'activité (Audit Trail)
     */
    public function getFilteredLogs(array $filters = [], int $limit = 100): array
    {
        $sql = "
            SELECT l.*, u.first_name, u.last_name, u.email, r.name as role_name,
                   p.name as project_name, p.code_prefix,
                   t.title as task_title
            FROM `{$this->table}` l
            LEFT JOIN `users` u ON l.user_id = u.id
            LEFT JOIN `roles` r ON u.role_id = r.id
            LEFT JOIN `projects` p ON l.project_id = p.id
            LEFT JOIN `tasks` t ON l.task_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND l.action LIKE :action";
            $params['action'] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND l.project_id = :project_id";
            $params['project_id'] = (int)$filters['project_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND l.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND l.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= " ORDER BY l.id DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
