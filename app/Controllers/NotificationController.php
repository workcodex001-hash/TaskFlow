<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté
     */
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $userId = AuthMiddleware::id();
        $unreadOnly = $this->request->get('filter') === 'unread';

        $notifModel = new Notification();
        $notifications = $notifModel->getForUser($userId, $unreadOnly, 50);
        $unreadCount = $notifModel->countUnread($userId);

        // Si requête AJAX JSON (ex: menu déroulant de la barre de navigation)
        if ($this->request->isAjax()) {
            Response::json([
                'success'       => true,
                'unread_count'  => $unreadCount,
                'notifications' => $notifications
            ]);
            return;
        }

        $this->render('notifications/index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'unreadOnly'    => $unreadOnly
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(string $id): void
    {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();

        $userId = AuthMiddleware::id();
        (new Notification())->markAsRead((int)$id, $userId);

        if ($this->request->isAjax()) {
            Response::json(['success' => true]);
            return;
        }

        Session::setFlash('success', 'Notification marquée comme lue.');
        Response::redirect('/notifications');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): void
    {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();

        $userId = AuthMiddleware::id();
        (new Notification())->markAllAsRead($userId);

        if ($this->request->isAjax()) {
            Response::json(['success' => true]);
            return;
        }

        Session::setFlash('success', 'Toutes vos notifications ont été marquées comme lues.');
        Response::redirect('/notifications');
    }

    /**
     * Supprimer une notification
     */
    public function delete(string $id): void
    {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();

        $userId = AuthMiddleware::id();
        (new Notification())->deleteNotification((int)$id, $userId);

        Session::setFlash('success', 'Notification supprimée.');
        Response::redirect('/notifications');
    }
}
