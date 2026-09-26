<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Notification extends Model
{
    protected string $table = 'notifications';

    /**
     * Récupère les notifications d'un utilisateur avec tri chronologique inverse
     */
    public function getForUser(int $userId, bool $unreadOnly = false, int $limit = 30): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE user_id = :user_id";
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte les notifications non lues d'un utilisateur
     */
    public function countUnread(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Crée une notification pour un utilisateur
     */
    public function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $linkUrl = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO `{$this->table}` (user_id, type, title, message, link_url, is_read, created_at)
            VALUES (:user_id, :type, :title, :message, :link_url, 0, NOW())
        ");
        $stmt->execute([
            'user_id'  => $userId,
            'type'     => $type,
            'title'    => trim($title),
            'message'  => trim($message),
            'link_url' => $linkUrl
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Notifie plusieurs utilisateurs à la fois (ex: lors d'une assignation ou commentaire)
     */
    public function notifyMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $linkUrl = null
    ): void {
        foreach (array_unique($userIds) as $userId) {
            $this->createNotification((int)$userId, $type, $title, $message, $linkUrl);
        }
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Supprime une notification
     */
    public function deleteNotification(int $notificationId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
    }
}
