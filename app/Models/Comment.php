<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Comment extends Model
{
    protected string $table = 'comments';

    /**
     * Récupère les commentaires d'une tâche ordonnés chronologiquement avec les données de l'auteur
     */
    public function getCommentsForTask(int $taskId): array
    {
        $sql = "
            SELECT c.*, 
                   u.first_name, u.last_name, u.email, u.role_id,
                   r.label as role_label
            FROM `{$this->table}` c
            JOIN `users` u ON c.user_id = u.id
            JOIN `roles` r ON u.role_id = r.id
            WHERE c.task_id = :task_id
            ORDER BY c.created_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    public function createComment(int $taskId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO `{$this->table}` (task_id, user_id, content, created_at)
            VALUES (:task_id, :user_id, :content, NOW())
        ");
        $stmt->execute([
            'task_id' => $taskId,
            'user_id' => $userId,
            'content' => trim($content)
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteComment(int $commentId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = :id");
        return $stmt->execute(['id' => $commentId]);
    }
}
