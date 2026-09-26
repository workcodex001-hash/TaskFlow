<?php
declare(strict_types=1);

namespace App\Config;

/**
 * Configuration globale de l'application TaskFlow
 */
class App
{
    public const APP_NAME = 'TaskFlow';
    public const APP_VERSION = '1.0.0';
    public const APP_URL = 'http://localhost/taskflow/public';

    // Rôles
    public const ROLE_ADMIN = 1;
    public const ROLE_MANAGER = 2;
    public const ROLE_MEMBER = 3;

    // Statuts de tâches
    public const STATUS_TODO = 'a_faire';
    public const STATUS_IN_PROGRESS = 'en_cours';
    public const STATUS_IN_REVIEW = 'en_revue';
    public const STATUS_DONE = 'terminee';
    public const STATUS_BLOCKED = 'bloquee';

    // Priorités
    public const PRIORITY_LOW = 'basse';
    public const PRIORITY_MEDIUM = 'moyenne';
    public const PRIORITY_HIGH = 'haute';
    public const PRIORITY_URGENT = 'urgente';

    /**
     * Helper d'échappement XSS universel pour les vues
     */
    public static function escape(?string $string): string
    {
        return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Raccourci global d'échappement HTML e()
 */
if (!function_exists('e')) {
    function e(?string $string): string
    {
        return \App\Config\App::escape($string);
    }
}
