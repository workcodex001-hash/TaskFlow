<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Models\Comment;
use App\Models\Task;
use App\Models\ActivityLog;

class CommentController extends Controller
{
    public function store(string $taskId): void
    {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();

        $tId = (int)$taskId;
        $content = trim((string)$this->request->post('content', ''));
        $currentUserId = AuthMiddleware::id();

        if (empty($content)) {
            Session::setFlash('danger', 'Le contenu du commentaire ne peut pas être vide.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? "/taskflow/public/tasks/{$tId}");
        }

        $taskModel = new Task();
        $task = $taskModel->findById($tId);

        if (!$task) {
            Response::abort(404, 'Tâche introuvable.');
        }

        $commentModel = new Comment();
        $commentId = $commentModel->createComment($tId, $currentUserId, $content);

        // Audit Log
        $logModel = new ActivityLog();
        $logModel->log('COMMENT_ADD', $currentUserId, (int)$task['project_id'], $tId, [
            'comment_id' => $commentId,
            'excerpt'    => substr($content, 0, 60)
        ]);

        Session::setFlash('success', 'Commentaire publié.');
        Response::redirect($_SERVER['HTTP_REFERER'] ?? "/taskflow/public/tasks/{$tId}");
    }

    public function delete(string $id): void
    {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();

        $commentId = (int)$id;
        $commentModel = new Comment();
        $comment = $commentModel->findById($commentId);

        if (!$comment) {
            Response::abort(404, 'Commentaire introuvable.');
        }

        $currentUserId = AuthMiddleware::id();

        // Seul l'auteur du commentaire ou un administrateur peut supprimer
        if ($comment['user_id'] !== $currentUserId && !AuthMiddleware::isAdmin()) {
            Response::abort(403, 'Vous n\'êtes pas autorisé à supprimer ce commentaire.');
        }

        $commentModel->deleteComment($commentId);

        Session::setFlash('info', 'Commentaire supprimé.');
        Response::redirect($_SERVER['HTTP_REFERER'] ?? '/taskflow/public/dashboard');
    }
}
