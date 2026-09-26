<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\ActivityLog;

class TaskController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $user = AuthMiddleware::user();

        $projectModel = new Project();
        $projects = $projectModel->getProjectsForUser((int)$user['id'], AuthMiddleware::isAdmin());

        $userModel = new User();
        $allUsers = $userModel->getAllUsersWithRoles();

        $filters = [
            'project_id'       => $this->request->get('project_id'),
            'status'           => $this->request->get('status'),
            'priority'         => $this->request->get('priority'),
            'assigned_user_id' => $this->request->get('assigned_user_id')
        ];

        $taskModel = new Task();
        $tasks = $taskModel->getTasksWithDetails(array_filter($filters));

        $this->render('tasks/index', [
            'pageTitle' => 'Tâches',
            'tasks'     => $tasks,
            'projects'  => $projects,
            'users'     => $allUsers,
            'filters'   => $filters
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::requireRole([1, 2]); // Admin ou Manager

        $projectId = (int)$this->request->get('project_id', 0);
        $projectModel = new Project();
        $projects = $projectModel->getProjectsForUser(AuthMiddleware::id(), AuthMiddleware::isAdmin());

        $userModel = new User();
        $users = $userModel->getAllUsersWithRoles();

        $this->render('tasks/form', [
            'pageTitle'       => 'Nouvelle Tâche',
            'projects'        => $projects,
            'selectedProject' => $projectId,
            'users'           => $users,
            'task'            => null
        ]);
    }

    public function store(): void
    {
        AuthMiddleware::requireRole([1, 2]);
        $this->validateCsrf();

        $projectId = (int)$this->request->post('project_id');
        $title = trim((string)$this->request->post('title', ''));
        $description = trim((string)$this->request->post('description', ''));
        $priority = (string)$this->request->post('priority', 'moyenne');
        $status = (string)$this->request->post('status', 'a_faire');
        $dueDate = (string)$this->request->post('due_date', '');
        $estimatedHours = (float)$this->request->post('estimated_hours', 0.0);
        $blockedReason = trim((string)$this->request->post('blocked_reason', ''));
        $assigneeIds = (array)($this->request->post('assignee_ids') ?? []);

        if (empty($projectId) || empty($title)) {
            Session::setFlash('danger', 'Veuillez sélectionner un projet et saisir un intitulé pour la tâche.');
            Response::redirect('/taskflow/public/tasks/create');
        }

        if ($status === 'bloquee' && empty($blockedReason)) {
            Session::setFlash('danger', 'Un motif est strictement requis pour définir le statut « Bloquée ».');
            Response::redirect('/taskflow/public/tasks/create');
        }

        $taskModel = new Task();
        $taskId = $taskModel->createTask([
            'project_id'      => $projectId,
            'creator_id'      => AuthMiddleware::id(),
            'title'           => $title,
            'description'     => $description,
            'priority'        => $priority,
            'status'          => $status,
            'due_date'        => $dueDate ?: null,
            'estimated_hours' => $estimatedHours,
            'blocked_reason'  => $blockedReason
        ], array_map('intval', $assigneeIds));

        // Audit Log
        $logModel = new ActivityLog();
        $logModel->log('TASK_CREATE', AuthMiddleware::id(), $projectId, $taskId, [
            'title'     => $title,
            'priority'  => $priority,
            'assignees' => $assigneeIds
        ]);

        Session::setFlash('success', "Tâche « {$title} » créée avec succès.");
        Response::redirect("/taskflow/public/projects/{$projectId}");
    }

    public function show(string $id): void
    {
        AuthMiddleware::requireAuth();
        $taskId = (int)$id;

        $taskModel = new Task();
        $task = $taskModel->findWithAssignees($taskId);

        if (!$task) {
            Response::abort(404, 'Tâche introuvable.');
        }

        $this->render('tasks/show', [
            'pageTitle' => $task['title'],
            'task'      => $task
        ]);
    }

    public function updateStatus(string $id): void
    {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();

        $taskId = (int)$id;
        $status = (string)$this->request->post('status');
        $blockedReason = trim((string)$this->request->post('blocked_reason', ''));

        if ($status === 'bloquee' && empty($blockedReason)) {
            Session::setFlash('danger', 'Un motif de blocage est obligatoire pour passer cette tâche au statut « Bloquée ».');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? "/taskflow/public/tasks/{$taskId}");
        }

        $taskModel = new Task();
        $task = $taskModel->findById($taskId);

        if (!$task) {
            Response::abort(404, 'Tâche introuvable.');
        }

        $taskModel->updateStatus($taskId, $status, $blockedReason);

        // Audit Log
        $logModel = new ActivityLog();
        $logModel->log('TASK_STATUS_CHANGE', AuthMiddleware::id(), (int)$task['project_id'], $taskId, [
            'old_status'     => $task['status'],
            'new_status'     => $status,
            'blocked_reason' => $blockedReason
        ]);

        Session::setFlash('success', 'Statut de la tâche actualisé.');
        Response::redirect($_SERVER['HTTP_REFERER'] ?? "/taskflow/public/tasks/{$taskId}");
    }
}
