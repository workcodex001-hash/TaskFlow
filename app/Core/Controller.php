<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Contrôleur de base
 */
abstract class Controller
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Rend une vue avec injection de variables et mise en page globale
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'layouts/header'): void
    {
        // Extraction des variables pour la vue
        extract($data);

        // Variables globales accessibles partout
        $currentUser = AuthMiddleware::user();
        $flashes = Session::getFlashes();
        $csrfField = Csrf::field();

        // Bufferisation
        ob_start();
        $fullPath = dirname(__DIR__) . '/Views/' . ltrim($viewPath, '/') . '.php';
        if (!file_exists($fullPath)) {
            Response::abort(500, "Vue introuvable : {$viewPath}");
        }

        require_once dirname(__DIR__) . '/Views/layouts/header.php';
        require_once dirname(__DIR__) . '/Views/layouts/alerts.php';
        require $fullPath;
        require_once dirname(__DIR__) . '/Views/layouts/footer.php';

        $output = ob_get_clean();
        echo $output;
        exit;
    }

    /**
     * Vérifie obligatoirement le jeton CSRF pour les requêtes POST
     */
    protected function validateCsrf(): void
    {
        $token = $this->request->getCsrfToken();
        if (!Csrf::validate($token)) {
            Session::setFlash('danger', 'Session ou jeton de sécurité expiré. Veuillez réessayer.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? '/taskflow/public/login');
        }
    }
}
