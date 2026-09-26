<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Routeur HTTP léger et déclaratif avec gestion des paramètres d'URL {id}
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $handler, array $roles = []): void
    {
        $this->addRoute('GET', $path, $handler, $roles);
    }

    public function post(string $path, string $handler, array $roles = []): void
    {
        $this->addRoute('POST', $path, $handler, $roles);
    }

    private function addRoute(string $method, string $path, string $handler, array $roles): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => '/' . trim($path, '/'),
            'handler' => $handler,
            'roles'   => $roles
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Si le script est exécuté dans un sous-dossier (ex: /taskflow/public/)
        $basePath = '/taskflow/public';
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Conversion de {param} en regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                // Filtrer les clés numériques
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Vérification du contrôle d'accès / rôles
                if (!empty($route['roles'])) {
                    AuthMiddleware::requireRole($route['roles']);
                }

                // Exécution du contrôleur
                [$controllerName, $actionName] = explode('@', $route['handler']);
                $fullControllerClass = "App\\Controllers\\{$controllerName}";

                if (!class_exists($fullControllerClass)) {
                    Response::abort(500, "Contrôleur [{$fullControllerClass}] introuvable.");
                }

                $controller = new $fullControllerClass($request);

                if (!method_exists($controller, $actionName)) {
                    Response::abort(500, "Action [{$actionName}] introuvable dans le contrôleur.");
                }

                call_user_func_array([$controller, $actionName], $params);
                return;
            }
        }

        Response::abort(404, "La page demandée n'existe pas : [{$method}] {$uri}");
    }
}
