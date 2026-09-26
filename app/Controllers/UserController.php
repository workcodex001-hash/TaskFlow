<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;

class UserController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireRole([1]); // Réservé aux Administrateurs

        $userModel = new User();
        $roleModel = new Role();

        $users = $userModel->getAllUsersWithRoles();
        $roles = $roleModel->all();

        $this->render('users/index', [
            'pageTitle' => 'Gestion des Utilisateurs',
            'users'     => $users,
            'roles'     => $roles
        ]);
    }

    public function updateRole(): void
    {
        AuthMiddleware::requireRole([1]);
        $this->validateCsrf();

        $userId = (int)$this->request->post('user_id');
        $newRoleId = (int)$this->request->post('role_id');

        $userModel = new User();
        $targetUser = $userModel->findById($userId);

        if (!$targetUser) {
            Session::setFlash('danger', 'Utilisateur introuvable.');
            Response::redirect('/taskflow/public/admin/users');
        }

        // Empêcher l'admin de se rétrograder lui-même s'il est le seul admin
        $currentAdmin = AuthMiddleware::user();
        if ((int)$currentAdmin['id'] === $userId && $newRoleId !== 1) {
            Session::setFlash('danger', 'Vous ne pouvez pas révoquer vos propres droits administrateur.');
            Response::redirect('/taskflow/public/admin/users');
        }

        $userModel->updateRole($userId, $newRoleId);

        // Audit Log
        $logModel = new ActivityLog();
        $logModel->log('USER_ROLE_CHANGED', (int)$currentAdmin['id'], null, null, [
            'target_user_id' => $userId,
            'old_role_id'    => $targetUser['role_id'],
            'new_role_id'    => $newRoleId
        ]);

        Session::setFlash('success', "Le rôle de {$targetUser['first_name']} {$targetUser['last_name']} a été mis à jour.");
        Response::redirect('/taskflow/public/admin/users');
    }

    public function toggleStatus(): void
    {
        AuthMiddleware::requireRole([1]);
        $this->validateCsrf();

        $userId = (int)$this->request->post('user_id');
        $currentAdmin = AuthMiddleware::user();

        if ((int)$currentAdmin['id'] === $userId) {
            Session::setFlash('danger', 'Vous ne pouvez pas désactiver votre propre compte administrateur.');
            Response::redirect('/taskflow/public/admin/users');
        }

        $userModel = new User();
        $targetUser = $userModel->findById($userId);

        if (!$targetUser) {
            Session::setFlash('danger', 'Utilisateur introuvable.');
            Response::redirect('/taskflow/public/admin/users');
        }

        $newStatus = ((int)$targetUser['is_active'] === 1) ? 0 : 1;
        $userModel->toggleActive($userId, $newStatus);

        $statusLabel = $newStatus === 1 ? 'réactivé' : 'suspendu';

        // Audit Log
        $logModel = new ActivityLog();
        $logModel->log('USER_STATUS_TOGGLED', (int)$currentAdmin['id'], null, null, [
            'target_user_id' => $userId,
            'new_status'     => $newStatus
        ]);

        Session::setFlash('info', "Le compte de {$targetUser['first_name']} {$targetUser['last_name']} a été {$statusLabel}.");
        Response::redirect('/taskflow/public/admin/users');
    }
}
