<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\ActivityLog;

class ProjectController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $user = AuthMiddleware::user();

        $projectModel = new Project();
        $projects = $projectModel->getProjectsForUser((int)$user['id'], AuthMiddleware::isAdmin());

        $this->render('projects/index', [
            'pageTitle' => 'Projets',
            'projects'  => $projects,
            'user'      => $user
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::requireRole([1, 2]); // Admin ou Manager uniquement

        $userModel = new User();
        $allUsers = $userModel->getAllUsersWithRoles();

        $this->render('projects/form', [
            'pageTitle' => 'Nouveau Projet',
            'users'     => $allUsers,
            'project'   => null
        ]);
    }

    public function store(): void
    {
        AuthMiddleware::requireRole([1, 2]);
        $this->validateCsrf();

        $name = trim((string)$this->request->post('name', ''));
        $codePrefix = strtoupper(trim((string)$this->request->post('code_prefix', '')));
        $description = trim((string)$this->request->post('description', ''));
        $ownerId = (int)$this->request->post('owner_id', AuthMiddleware::id());
        $startDate = (string)$this->request->post('start_date', '');
        $dueDate = (string)$this->request->post('due_date', '');
        $memberIds = (array)($this->request->post('member_ids') ?? []);

        if (empty($name) || empty($codePrefix)) {
            Session::setFlash('danger', 'Le nom du projet et le code préfixe sont obligatoires.');
            Response::redirect('/taskflow/public/projects/create');
        }

        $projectModel = new Project();
        $projectId = $projectModel->createProject([
            'name'        => $name,
            'code_prefix' => $codePrefix,
            'description' => $description,
            'owner_id'    => $ownerId,
            'status'      => 'actif',
            'start_date'  => $startDate ?: null,
            'due_date'    => $dueDate ?: null
        ], array_map('intval', $memberIds));

        // Audit Log
        $logModel = new ActivityLog();
        $logModel->log('PROJECT_CREATE', AuthMiddleware::id(), $projectId, null, [
            'name'        => $name,
            'code_prefix' => $codePrefix
        ]);

        Session::setFlash('success', "Projet « {$name} » créé avec succès.");
        Response::redirect("/taskflow/public/projects/{$projectId}");
    }

    public function show(string $id): void
    {
        AuthMiddleware::requireAuth();
        $projectId = (int)$id;

        $projectModel = new Project();
        $project = $projectModel->findWithDetails($projectId);

        if (!$project) {
            Response::abort(404, 'Projet introuvable.');
        }

        // Vérification des droits d'accès au projet
        $currentUserId = AuthMiddleware::id();
        if (!AuthMiddleware::isAdmin() && (int)$project['owner_id'] !== $currentUserId && !$projectModel->isMember($projectId, $currentUserId)) {
            Response::abort(403, 'Vous ne faites pas partie de l\'équipe assignée à ce projet.');
        }

        $members = $projectModel->getMembers($projectId);

        $taskModel = new Task();
        $tasks = $taskModel->getTasksWithDetails(['project_id' => $projectId]);

        $this->render('projects/show', [
            'pageTitle' => "{$project['code_prefix']} - {$project['name']}",
            'project'   => $project,
            'members'   => $members,
            'tasks'     => $tasks
        ]);
    }
}
