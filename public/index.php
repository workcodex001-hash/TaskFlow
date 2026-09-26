<?php
declare(strict_types=1);

/**
 * TaskFlow Enterprise - Front Controller
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Autoloader PSR-4
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Environnement (.env)
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

use App\Core\Session;
use App\Core\Request;
use App\Core\Router;
use App\Config\App;

Session::start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

$request = new Request();
$router = new Router();

// Routes Publiques / Auth
$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Routes Protégées - Dashboard
$router->get('/dashboard', 'DashboardController@index', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);

// Routes Administration - Utilisateurs & Rôles (Admin)
$router->get('/admin/users', 'UserController@index', [App::ROLE_ADMIN]);
$router->post('/admin/users/role', 'UserController@updateRole', [App::ROLE_ADMIN]);
$router->post('/admin/users/toggle', 'UserController@toggleStatus', [App::ROLE_ADMIN]);

// Routes Lot 2 : Projets
$router->get('/projects', 'ProjectController@index', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->get('/projects/create', 'ProjectController@create', [App::ROLE_ADMIN, App::ROLE_MANAGER]);
$router->post('/projects', 'ProjectController@store', [App::ROLE_ADMIN, App::ROLE_MANAGER]);
$router->get('/projects/{id}', 'ProjectController@show', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);

// Routes Lot 2 : Tâches
$router->get('/tasks', 'TaskController@index', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->get('/tasks/create', 'TaskController@create', [App::ROLE_ADMIN, App::ROLE_MANAGER]);
$router->post('/tasks', 'TaskController@store', [App::ROLE_ADMIN, App::ROLE_MANAGER]);
$router->get('/tasks/{id}', 'TaskController@show', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->post('/tasks/{id}/status', 'TaskController@updateStatus', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);

// Routes Lot 3 : Commentaires
$router->post('/tasks/{id}/comments', 'CommentController@store', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->post('/comments/{id}/delete', 'CommentController@delete', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);

// Routes Lot 4 : Notifications & Audit Log
$router->get('/notifications', 'NotificationController@index', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->post('/notifications/{id}/read', 'NotificationController@markAsRead', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->post('/notifications/read-all', 'NotificationController@markAllAsRead', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->post('/notifications/{id}/delete', 'NotificationController@delete', [App::ROLE_ADMIN, App::ROLE_MANAGER, App::ROLE_MEMBER]);
$router->get('/audit', 'AuditController@index', [App::ROLE_ADMIN, App::ROLE_MANAGER]);
$router->get('/audit/export', 'AuditController@exportCsv', [App::ROLE_ADMIN]);

// Dispatch
$router->dispatch($request);
