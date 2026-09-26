<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\App;

/**
 * Middleware de contrôle d'accès et de vérification des rôles (RBAC)
 */
class AuthMiddleware
{
    public const USER_SESSION_KEY = '_auth_user';

    public static function check(): bool
    {
        Session::start();
        return Session::has(self::USER_SESSION_KEY);
    }

    public static function user(): ?array
    {
        Session::start();
        return Session::get(self::USER_SESSION_KEY);
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user['id'] ?? null;
    }

    public static function roleId(): ?int
    {
        $user = self::user();
        return $user['role_id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::roleId() === App::ROLE_ADMIN;
    }

    public static function isManager(): bool
    {
        return self::roleId() === App::ROLE_MANAGER;
    }

    public static function isMember(): bool
    {
        return self::roleId() === App::ROLE_MEMBER;
    }

    /**
     * Exige que l'utilisateur soit connecté
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::setFlash('warning', 'Veuillez vous connecter pour accéder à cette page.');
            Response::redirect('/taskflow/public/login');
        }
    }

    /**
     * Exige que l'utilisateur possède l'un des rôles autorisés
     */
    public static function requireRole(array $allowedRoleIds): void
    {
        self::requireAuth();
        $userRole = self::roleId();

        if (!in_array($userRole, $allowedRoleIds, true)) {
            Response::abort(403, "Vous ne disposez pas des privilèges nécessaires pour accéder à cette ressource.");
        }
    }

    public static function login(array $userData): void
    {
        Session::start();
        Session::regenerate(); // Contre la fixation de session
        Session::set(self::USER_SESSION_KEY, $userData);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
