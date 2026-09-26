<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Protection contre les attaques CSRF (Cross-Site Request Forgery)
 */
class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Génère ou récupère le token CSRF courant
     */
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get(self::TOKEN_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }
        return $token;
    }

    /**
     * Valide le jeton CSRF reçu (soit via POST, soit via Header HTTP)
     */
    public static function validate(?string $submittedToken): bool
    {
        Session::start();
        $storedToken = Session::get(self::TOKEN_KEY);
        if (!$storedToken || empty($submittedToken)) {
            return false;
        }

        return hash_equals($storedToken, $submittedToken);
    }

    /**
     * Renvoie le champ HTML caché prêt à l'emploi
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
