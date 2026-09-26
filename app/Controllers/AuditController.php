<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Response;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Project;

class AuditController extends Controller
{
    /**
     * Consultation du journal d'audit complet (Réservé aux Admins et Chefs de projet)
     */
    public function index(): void
    {
        AuthMiddleware::requireRole([1, 2]); // Admin ou Manager

        $filters = [
            'user_id'    => $this->request->get('user_id'),
            'action'     => $this->request->get('action'),
            'project_id' => $this->request->get('project_id'),
            'date_from'  => $this->request->get('date_from'),
            'date_to'    => $this->request->get('date_to'),
        ];

        $logModel = new ActivityLog();
        $logs = $logModel->getFilteredLogs(array_filter($filters), 150);

        $users = (new User())->getAllUsersWithRoles();
        $projects = (new Project())->all();

        $this->render('audit/index', [
            'pageTitle' => 'Journal d\'Audit & Traçabilité',
            'logs'      => $logs,
            'users'     => $users,
            'projects'  => $projects,
            'filters'   => $filters
        ]);
    }

    /**
     * Export des logs en format CSV conforme RGPD / ISO 27001
     */
    public function exportCsv(): void
    {
        AuthMiddleware::requireRole([1]); // Administrateurs uniquement

        $filters = [
            'user_id'    => $this->request->get('user_id'),
            'action'     => $this->request->get('action'),
            'project_id' => $this->request->get('project_id'),
            'date_from'  => $this->request->get('date_from'),
            'date_to'    => $this->request->get('date_to'),
        ];

        $logs = (new ActivityLog())->getFilteredLogs(array_filter($filters), 1000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="taskflow_audit_export_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        // BOM UTF-8 pour ouverture correcte dans Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID', 'Date & Heure', 'Utilisateur', 'Email', 'Action', 'Projet', 'Tâche', 'Adresse IP', 'Détails']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'Système / Anonyme',
                $log['email'] ?? 'N/A',
                $log['action'],
                $log['project_name'] ?? 'N/A',
                $log['task_title'] ?? 'N/A',
                $log['ip_address'],
                $log['details'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
