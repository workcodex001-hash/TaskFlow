<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Config\Database;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\ActivityLog;
use PDO;

class DashboardController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $currentUser = AuthMiddleware::user();
        $userId = (int)$currentUser['id'];
        $isAdmin = AuthMiddleware::isAdmin();

        $db = Database::getConnection();

        // 1. Projets actifs et avancement
        $projectModel = new Project();
        $projects = $projectModel->getProjectsForUser($userId, $isAdmin);

        // 2. Décompte des tâches par statut
        $statusStmt = $db->query("
            SELECT status, COUNT(*) as count 
            FROM `tasks` 
            GROUP BY status
        ");
        $statusCountsRaw = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $statusCounts = [
            'a_faire'   => (int)($statusCountsRaw['a_faire'] ?? 0),
            'en_cours'  => (int)($statusCountsRaw['en_cours'] ?? 0),
            'en_revue'  => (int)($statusCountsRaw['en_revue'] ?? 0),
            'terminee'  => (int)($statusCountsRaw['terminee'] ?? 0),
            'bloquee'   => (int)($statusCountsRaw['bloquee'] ?? 0),
        ];

        $totalTasks = array_sum($statusCounts);
        $completionRate = $totalTasks > 0 ? (int)round(($statusCounts['terminee'] / $totalTasks) * 100) : 0;

        // 3. Tâches en retard critique
        $overdueStmt = $db->query("
            SELECT t.*, p.name as project_name, p.code_prefix,
                   u.first_name as creator_first_name, u.last_name as creator_last_name
            FROM `tasks` t
            JOIN `projects` p ON t.project_id = p.id
            JOIN `users` u ON t.creator_id = u.id
            WHERE t.due_date < CURDATE() 
              AND t.status != 'terminee'
            ORDER BY t.due_date ASC
            LIMIT 5
        ");
        $overdueTasks = $overdueStmt->fetchAll();

        // 4. Charge de travail par collaborateur (nombre de tâches actives en cours / à faire)
        $workloadStmt = $db->query("
            SELECT u.id, u.first_name, u.last_name, u.email,
                   COUNT(ta.task_id) as active_tasks
            FROM `users` u
            LEFT JOIN `task_assignments` ta ON u.id = ta.user_id
            LEFT JOIN `tasks` t ON ta.task_id = t.id AND t.status != 'terminee'
            WHERE u.is_active = 1
            GROUP BY u.id
            ORDER BY active_tasks DESC
        ");
        $workload = $workloadStmt->fetchAll();

        // 5. Journal d'audit récent
        $logModel = new ActivityLog();
        $recentLogs = $logModel->getLatest(8);

        $this->render('dashboard/index', [
            'pageTitle'      => 'Tableau de bord',
            'user'           => $currentUser,
            'projects'       => $projects,
            'statusCounts'   => $statusCounts,
            'totalTasks'     => $totalTasks,
            'completionRate' => $completionRate,
            'overdueTasks'   => $overdueTasks,
            'workload'       => $workload,
            'recentLogs'     => $recentLogs
        ]);
    }
}
