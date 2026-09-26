<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Gestion des réponses HTTP, redirections et JSON
 */
class Response
{
    public static function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $title = match ($code) {
            403 => 'Accès Interdit (403)',
            404 => 'Page Non Trouvée (404)',
            500 => 'Erreur Interne du Serveur (500)',
            default => "Erreur {$code}"
        };

        echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>{$title}</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "</head><body class='bg-light d-flex align-items-center vh-100'>";
        echo "<div class='container text-center'><div class='card shadow-sm p-5 mx-auto' style='max-width: 500px;'>";
        echo "<h1 class='display-4 text-danger mb-3'>{$code}</h1>";
        echo "<h2 class='h4 mb-3'>{$title}</h2>";
        echo "<p class='text-muted mb-4'>" . htmlspecialchars($message ?: 'Une erreur est survenue.') . "</p>";
        echo "<a href='/taskflow/public/' class='btn btn-primary'>Retour à l'accueil</a>";
        echo "</div></div></body></html>";
        exit;
    }
}
