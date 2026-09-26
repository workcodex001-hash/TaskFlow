import React, { useState, useMemo } from 'react';
import {
  LayoutDashboard,
  FolderKanban,
  CheckSquare,
  Users,
  Shield,
  Search,
  Plus,
  Bell,
  Clock,
  AlertCircle,
  FileCode,
  Copy,
  Check,
  Filter,
  MessageSquare,
  Send,
  Trash2,
  Calendar,
  X,
  Download,
  Kanban,
  Table as TableIcon,
  ChevronRight,
  UserCheck,
  CheckCircle2,
  AlertTriangle,
  FolderPlus,
  Briefcase
} from 'lucide-react';

// ==============================================================================
// TYPES & INTERFACES
// ==============================================================================

interface TeamMember {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  roleId: number;
  roleName: 'admin' | 'manager' | 'member';
  roleLabel: string;
  department: string;
  isActive: boolean;
  lastLogin: string;
  createdAt: string;
}

interface Project {
  id: number;
  name: string;
  codePrefix: string;
  description: string;
  ownerId: number;
  ownerName: string;
  status: 'actif' | 'en_pause' | 'termine' | 'archive';
  startDate: string;
  dueDate: string;
  memberIds: number[];
}

interface TaskComment {
  id: number;
  taskId: number;
  userId: number;
  userName: string;
  userRole: string;
  content: string;
  timestamp: string;
}

interface Task {
  id: number;
  projectId: number;
  projectCode: string;
  title: string;
  description: string;
  priority: 'basse' | 'moyenne' | 'haute' | 'urgente';
  status: 'a_faire' | 'en_cours' | 'en_revue' | 'terminee' | 'bloquee';
  dueDate: string;
  estimatedHours: number;
  blockedReason?: string;
  assigneeIds: number[];
  createdAt: string;
}

interface NotificationItem {
  id: number;
  userId: number;
  type: 'ASSIGNMENT' | 'STATUS_CHANGE' | 'COMMENT' | 'DEADLINE' | 'SYSTEM';
  title: string;
  message: string;
  taskId?: number;
  isRead: boolean;
  createdAt: string;
}

interface AuditRecord {
  id: number;
  userId: number;
  userName: string;
  userEmail: string;
  action: string;
  category: 'AUTH' | 'RBAC' | 'PROJECT' | 'TASK' | 'COMMENT' | 'NOTIFICATION';
  description: string;
  targetInfo?: string;
  ipAddress: string;
  timestamp: string;
}

// ==============================================================================
// DONNÉES INITIALES
// ==============================================================================

const INITIAL_MEMBERS: TeamMember[] = [
  {
    id: 1,
    firstName: 'Alexandre',
    lastName: 'Dubois',
    email: 'admin@taskflow.local',
    roleId: 1,
    roleName: 'admin',
    roleLabel: 'Administrateur',
    department: 'Direction Technique',
    isActive: true,
    lastLogin: 'Aujourd\'hui à 11:42',
    createdAt: '15 Jan 2026'
  },
  {
    id: 2,
    firstName: 'Sophie',
    lastName: 'Vidal',
    email: 'manager@taskflow.local',
    roleId: 2,
    roleName: 'manager',
    roleLabel: 'Chef de projet',
    department: 'Produit & Opérations',
    isActive: true,
    lastLogin: 'Hier à 17:15',
    createdAt: '02 Fév 2026'
  },
  {
    id: 3,
    firstName: 'Lucas',
    lastName: 'Moreau',
    email: 'member@taskflow.local',
    roleId: 3,
    roleName: 'member',
    roleLabel: 'Membre',
    department: 'Ingénierie Frontend',
    isActive: true,
    lastLogin: 'Il y a 3 heures',
    createdAt: '12 Fév 2026'
  },
  {
    id: 4,
    firstName: 'Camille',
    lastName: 'Roux',
    email: 'camille.roux@taskflow.local',
    roleId: 3,
    roleName: 'member',
    roleLabel: 'Membre',
    department: 'Design & UX',
    isActive: true,
    lastLogin: 'Il y a 1 heure',
    createdAt: '28 Fév 2026'
  }
];

const INITIAL_PROJECTS: Project[] = [
  {
    id: 1,
    name: 'Refonte Portail Client B2B',
    codePrefix: 'PORTAL',
    description: 'Modernisation de l\'espace client entreprise, API REST et architecture d\'authentification SSO.',
    ownerId: 2,
    ownerName: 'Sophie Vidal',
    status: 'actif',
    startDate: '2026-09-01',
    dueDate: '2026-10-30',
    memberIds: [1, 2, 3, 4]
  },
  {
    id: 2,
    name: 'Infrastructure Cloud & Sécurité',
    codePrefix: 'INFRA',
    description: 'Cluster MySQL MariaDB, réplication et renforcement des politiques RBAC.',
    ownerId: 1,
    ownerName: 'Alexandre Dubois',
    status: 'actif',
    startDate: '2026-09-10',
    dueDate: '2026-11-15',
    memberIds: [1, 2, 3]
  }
];

const INITIAL_TASKS: Task[] = [
  {
    id: 101,
    projectId: 1,
    projectCode: 'PORTAL-01',
    title: 'Définition des schémas d\'authentification Bcrypt',
    description: 'Mettre en place les sessions sécurisées avec flags HttpOnly et SameSite=Lax.',
    priority: 'haute',
    status: 'terminee',
    dueDate: '2026-09-24',
    estimatedHours: 6.0,
    assigneeIds: [1, 3],
    createdAt: '2026-09-20'
  },
  {
    id: 102,
    projectId: 1,
    projectCode: 'PORTAL-02',
    title: 'Développement de la console d\'administration des rôles',
    description: 'Interface de promotion et suspension des comptes avec traçabilité dans l\'audit log.',
    priority: 'urgente',
    status: 'en_cours',
    dueDate: '2026-09-28',
    estimatedHours: 12.0,
    assigneeIds: [2, 3],
    createdAt: '2026-09-22'
  },
  {
    id: 103,
    projectId: 1,
    projectCode: 'PORTAL-03',
    title: 'Spécification de l\'API d\'exportation des livrables',
    description: 'Attente de confirmation du format CSV/JSON requis par le pôle comptabilité.',
    priority: 'moyenne',
    status: 'bloquee',
    blockedReason: 'Validation du format comptable en attente de réunion client.',
    dueDate: '2026-09-25',
    estimatedHours: 4.5,
    assigneeIds: [2],
    createdAt: '2026-09-23'
  },
  {
    id: 104,
    projectId: 1,
    projectCode: 'PORTAL-04',
    title: 'Revue ergonomique des formulaires de saisie',
    description: 'Uniformisation des labels, suppression des éléments superflus et accessibilité WCAG AA.',
    priority: 'moyenne',
    status: 'en_revue',
    dueDate: '2026-09-29',
    estimatedHours: 5.0,
    assigneeIds: [4],
    createdAt: '2026-09-24'
  },
  {
    id: 105,
    projectId: 2,
    projectCode: 'INFRA-01',
    title: 'Audit des index et requêtes préparées PDO',
    description: 'Vérification de la désactivation d\'émulation des requêtes sur l\'ensemble des modèles.',
    priority: 'haute',
    status: 'a_faire',
    dueDate: '2026-10-05',
    estimatedHours: 8.0,
    assigneeIds: [1, 3],
    createdAt: '2026-09-25'
  }
];

const INITIAL_COMMENTS: TaskComment[] = [
  {
    id: 1,
    taskId: 103,
    userId: 2,
    userName: 'Sophie Vidal',
    userRole: 'Chef de projet',
    content: 'J\'ai envoyé une relance au responsable comptable pour débloquer la spécification des colonnes.',
    timestamp: 'Hier à 14:30'
  },
  {
    id: 2,
    taskId: 103,
    userId: 1,
    userName: 'Alexandre Dubois',
    userRole: 'Administrateur',
    content: 'Parfait, si nous n\'avons pas de retour d\'ici vendredi, nous basculerons sur le format générique JSON UTF-8.',
    timestamp: 'Hier à 16:15'
  },
  {
    id: 3,
    taskId: 102,
    userId: 3,
    userName: 'Lucas Moreau',
    userRole: 'Membre',
    content: 'Les tests unitaires sur les règles RBAC sont passés au vert. Reste l\'intégration du middleware côté routeur.',
    timestamp: 'Aujourd\'hui à 09:12'
  }
];

const INITIAL_NOTIFICATIONS: NotificationItem[] = [
  {
    id: 201,
    userId: 1,
    type: 'DEADLINE',
    title: 'Échéance dépassée',
    message: 'La tâche PORTAL-03 a dépassé son échéance du 25/09.',
    taskId: 103,
    isRead: false,
    createdAt: 'Aujourd\'hui 08:30'
  },
  {
    id: 202,
    userId: 1,
    type: 'COMMENT',
    title: 'Nouveau commentaire',
    message: 'Lucas Moreau a commenté la tâche PORTAL-02.',
    taskId: 102,
    isRead: false,
    createdAt: 'Aujourd\'hui 09:12'
  },
  {
    id: 203,
    userId: 1,
    type: 'ASSIGNMENT',
    title: 'Assignation de tâche',
    message: 'Vous avez été assigné à la tâche INFRA-01.',
    taskId: 105,
    isRead: true,
    createdAt: '25 Sep 14:00'
  }
];

const INITIAL_AUDIT_LOGS: AuditRecord[] = [
  {
    id: 405,
    userId: 2,
    userName: 'Sophie Vidal',
    userEmail: 'manager@taskflow.local',
    action: 'TASK_STATUS_UPDATE',
    category: 'TASK',
    description: 'Statut passé à [bloquee] avec justification requise',
    targetInfo: 'Tâche PORTAL-03',
    ipAddress: '192.168.1.88',
    timestamp: '25 Sep 14:20'
  },
  {
    id: 404,
    userId: 1,
    userName: 'Alexandre Dubois',
    userEmail: 'admin@taskflow.local',
    action: 'COMMENT_CREATE',
    category: 'COMMENT',
    description: 'Publication de note d\'arbitrage technique',
    targetInfo: 'Tâche PORTAL-03',
    ipAddress: '192.168.1.45',
    timestamp: '25 Sep 16:15'
  },
  {
    id: 403,
    userId: 3,
    userName: 'Lucas Moreau',
    userEmail: 'member@taskflow.local',
    action: 'AUTH_LOGIN',
    category: 'AUTH',
    description: 'Connexion réussie avec régénération de token CSRF',
    targetInfo: 'Session membre #3',
    ipAddress: '192.168.1.62',
    timestamp: '26 Sep 09:10'
  },
  {
    id: 402,
    userId: 1,
    userName: 'Alexandre Dubois',
    userEmail: 'admin@taskflow.local',
    action: 'ROLE_UPDATE',
    category: 'RBAC',
    description: 'Vérification des droits d\'accès et permissions globales',
    targetInfo: 'Matrice des rôles',
    ipAddress: '192.168.1.45',
    timestamp: '26 Sep 10:15'
  }
];

const PHP_FILES: Record<string, { path: string; desc: string; type: string; content: string }> = {
  'app/Models/Notification.php': {
    path: '/app/Models/Notification.php',
    type: 'PHP Model',
    desc: 'Modèle de persistance et comptage des notifications utilisateur',
    content: `<?php
declare(strict_types=1);
namespace App\\Models;

use App\\Core\\Model;
use PDO;

class Notification extends Model
{
    protected string $table = 'notifications';

    public function getForUser(int $userId, bool $unreadOnly = false, int $limit = 30): array {
        $sql = "SELECT * FROM \`{$this->table}\` WHERE user_id = :user_id";
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM \`{$this->table}\` WHERE user_id = :u AND is_read = 0");
        $stmt->execute(['u' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function createNotification(int $userId, string $type, string $title, string $message, ?string $link = null): int {
        $stmt = $this->db->prepare("INSERT INTO \`{$this->table}\` (user_id, type, title, message, link_url, created_at) VALUES (:u, :t, :ti, :m, :l, NOW())");
        $stmt->execute(['u' => $userId, 't' => $type, 'ti' => trim($title), 'm' => trim($message), 'l' => $link]);
        return (int)$this->db->lastInsertId();
    }
}`
  },
  'app/Controllers/NotificationController.php': {
    path: '/app/Controllers/NotificationController.php',
    type: 'PHP Controller',
    desc: 'Contrôleur du centre de notifications avec support AJAX et lecture',
    content: `<?php
declare(strict_types=1);
namespace App\\Controllers;

use App\\Core\\Controller;
use App\\Core\\AuthMiddleware;
use App\\Core\\Response;
use App\\Models\\Notification;

class NotificationController extends Controller
{
    public function index(): void {
        AuthMiddleware::requireAuth();
        $userId = AuthMiddleware::id();
        $notifModel = new Notification();

        $notifications = $notifModel->getForUser($userId, false, 50);
        $unreadCount = $notifModel->countUnread($userId);

        if ($this->request->isAjax()) {
            Response::json(['unread_count' => $unreadCount, 'notifications' => $notifications]);
            return;
        }

        $this->render('notifications/index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(string $id): void {
        AuthMiddleware::requireAuth();
        $this->validateCsrf();
        (new Notification())->markAsRead((int)$id, AuthMiddleware::id());
        Response::redirect('/notifications');
    }
}`
  },
  'app/Controllers/AuditController.php': {
    path: '/app/Controllers/AuditController.php',
    type: 'PHP Controller',
    desc: 'Consultation du journal d\'audit avec filtres et export CSV',
    content: `<?php
declare(strict_types=1);
namespace App\\Controllers;

use App\\Core\\Controller;
use App\\Core\\AuthMiddleware;
use App\\Models\\ActivityLog;

class AuditController extends Controller
{
    public function index(): void {
        AuthMiddleware::requireRole([1, 2]); // Admin ou Chef de projet
        $filters = array_filter([
            'user_id' => $this->request->get('user_id'),
            'action'  => $this->request->get('action')
        ]);
        $logs = (new ActivityLog())->getFilteredLogs($filters, 150);
        $this->render('audit/index', compact('logs', 'filters'));
    }

    public function exportCsv(): void {
        AuthMiddleware::requireRole([1]); // Administrateur uniquement
        $logs = (new ActivityLog())->getFilteredLogs([], 2000);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Date', 'Utilisateur', 'Action', 'IP']);
        foreach ($logs as $l) {
            fputcsv($out, [$l['id'], $l['created_at'], $l['first_name'] . ' ' . $l['last_name'], $l['action'], $l['ip_address']]);
        }
        fclose($out);
        exit;
    }
}`
  },
  'database/schema.sql': {
    path: '/database/schema.sql',
    type: 'SQL DDL',
    desc: 'Schéma relationnel complet (users, roles, projects, tasks, comments, notifications, activity_logs)',
    content: `-- Schema relationnel MySQL / MariaDB avec cles etrangeres et contraintes
CREATE TABLE \`notifications\` (
    \`id\` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    \`user_id\` INT UNSIGNED NOT NULL,
    \`type\` VARCHAR(40) NOT NULL,
    \`title\` VARCHAR(120) NOT NULL,
    \`message\` TEXT NOT NULL,
    \`link_url\` VARCHAR(255) NULL,
    \`is_read\` TINYINT(1) NOT NULL DEFAULT 0,
    \`created_at\` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT \`fk_notif_user\` FOREIGN KEY (\`user_id\`) REFERENCES \`users\` (\`id\`) ON DELETE CASCADE
);`
  }
};

// ==============================================================================
// COMPOSANT PRINCIPAL SAAS TASKFLOW
// ==============================================================================

export default function App() {
  // Navigation & Vues
  const [currentView, setCurrentView] = useState<'dashboard' | 'projects' | 'tasks' | 'team' | 'audit' | 'source'>('dashboard');
  const [members, setMembers] = useState<TeamMember[]>(INITIAL_MEMBERS);
  const [projects, setProjects] = useState<Project[]>(INITIAL_PROJECTS);
  const [tasks, setTasks] = useState<Task[]>(INITIAL_TASKS);
  const [comments, setComments] = useState<TaskComment[]>(INITIAL_COMMENTS);
  const [notifications, setNotifications] = useState<NotificationItem[]>(INITIAL_NOTIFICATIONS);
  const [auditLogs, setAuditLogs] = useState<AuditRecord[]>(INITIAL_AUDIT_LOGS);
  const [currentUser, setCurrentUser] = useState<TeamMember>(INITIAL_MEMBERS[0]);

  // Notifications Popover
  const [isNotifOpen, setIsNotifOpen] = useState(false);
  const [notifFilter, setNotifFilter] = useState<'all' | 'unread'>('all');

  // Filtres & Recherche
  const [globalSearch, setGlobalSearch] = useState('');
  const [filterProject, setFilterProject] = useState<number | 'all'>('all');
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [filterPriority, setFilterPriority] = useState<string>('all');
  const [filterAssignee, setFilterAssignee] = useState<number | 'all'>('all');
  const [taskViewMode, setTaskViewMode] = useState<'table' | 'kanban'>('table');

  // Filtres Audit Log
  const [auditUserFilter, setAuditUserFilter] = useState<string>('all');
  const [auditCategoryFilter, setAuditCategoryFilter] = useState<string>('all');
  const [auditSearch, setAuditSearch] = useState('');

  // Modales
  const [selectedTask, setSelectedTask] = useState<Task | null>(null);
  const [isNewTaskModalOpen, setIsNewTaskModalOpen] = useState(false);
  const [isNewProjectModalOpen, setIsNewProjectModalOpen] = useState(false);
  const [blockingTask, setBlockingTask] = useState<Task | null>(null);
  const [blockReasonInput, setBlockReasonInput] = useState('');

  // Formulaire nouvelle tâche
  const [newTaskTitle, setNewTaskTitle] = useState('');
  const [newTaskProjectId, setNewTaskProjectId] = useState<number>(1);
  const [newTaskPriority, setNewTaskPriority] = useState<'basse' | 'moyenne' | 'haute' | 'urgente'>('moyenne');
  const [newTaskDueDate, setNewTaskDueDate] = useState('2026-10-10');
  const [newTaskHours, setNewTaskHours] = useState(4.0);
  const [newTaskAssigneeIds, setNewTaskAssigneeIds] = useState<number[]>([3]);
  const [newTaskDesc, setNewTaskDesc] = useState('');

  // Formulaire nouveau projet
  const [newProjName, setNewProjName] = useState('');
  const [newProjCode, setNewProjCode] = useState('');
  const [newProjDesc, setNewProjDesc] = useState('');
  const [newProjDueDate, setNewProjDueDate] = useState('2026-11-30');

  // Commentaires
  const [newCommentText, setNewCommentText] = useState('');

  // Code inspection
  const [selectedFileKey, setSelectedFileKey] = useState<string>('app/Models/Notification.php');
  const [copiedKey, setCopiedKey] = useState<string | null>(null);

  // Bannière notification discrète
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => {
      setToastMessage(null);
    }, 3500);
  };

  // Calcul du nombre de notifications non lues
  const unreadCount = useMemo(() => {
    return notifications.filter(n => n.userId === currentUser.id && !n.isRead).length;
  }, [notifications, currentUser.id]);

  // Notifications filtrées pour le menu
  const userNotifications = useMemo(() => {
    const list = notifications.filter(n => n.userId === currentUser.id);
    if (notifFilter === 'unread') {
      return list.filter(n => !n.isRead);
    }
    return list;
  }, [notifications, currentUser.id, notifFilter]);

  // Action : marquer une notification comme lue
  const handleMarkNotificationRead = (notifId: number) => {
    setNotifications(prev =>
      prev.map(n => (n.id === notifId ? { ...n, isRead: true } : n))
    );
  };

  // Action : tout marquer comme lu
  const handleMarkAllNotificationsRead = () => {
    setNotifications(prev =>
      prev.map(n => (n.userId === currentUser.id ? { ...n, isRead: true } : n))
    );
    showToast('Toutes vos notifications sont marquées comme lues.');
  };

  // Action : ajouter un commentaire
  const handleAddComment = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedTask || !newCommentText.trim()) return;

    const newComment: TaskComment = {
      id: Date.now(),
      taskId: selectedTask.id,
      userId: currentUser.id,
      userName: `${currentUser.firstName} ${currentUser.lastName}`,
      userRole: currentUser.roleLabel,
      content: newCommentText.trim(),
      timestamp: 'À l\'instant'
    };

    setComments(prev => [...prev, newComment]);

    // Journal d'audit
    const auditRecord: AuditRecord = {
      id: Date.now(),
      userId: currentUser.id,
      userName: `${currentUser.firstName} ${currentUser.lastName}`,
      userEmail: currentUser.email,
      action: 'COMMENT_CREATE',
      category: 'COMMENT',
      description: `Commentaire sur ${selectedTask.projectCode}`,
      targetInfo: selectedTask.title,
      ipAddress: '192.168.1.45',
      timestamp: 'À l\'instant'
    };
    setAuditLogs(prev => [auditRecord, ...prev]);

    // Créer des notifications pour les assignés de la tâche
    selectedTask.assigneeIds.forEach(assigneeId => {
      if (assigneeId !== currentUser.id) {
        setNotifications(prev => [
          {
            id: Date.now() + assigneeId,
            userId: assigneeId,
            type: 'COMMENT',
            title: `Nouveau message sur ${selectedTask.projectCode}`,
            message: `${currentUser.firstName} ${currentUser.lastName} a laissé un commentaire.`,
            taskId: selectedTask.id,
            isRead: false,
            createdAt: 'À l\'instant'
          },
          ...prev
        ]);
      }
    });

    setNewCommentText('');
    showToast('Commentaire enregistré.');
  };

  // Action : changement de statut de tâche
  const handleStatusChange = (task: Task, newStatus: Task['status']) => {
    if (newStatus === 'bloquee') {
      setBlockingTask(task);
      setBlockReasonInput('');
      return;
    }

    applyStatusUpdate(task, newStatus, undefined);
  };

  const applyStatusUpdate = (task: Task, newStatus: Task['status'], reason?: string) => {
    setTasks(prev =>
      prev.map(t =>
        t.id === task.id
          ? {
              ...t,
              status: newStatus,
              blockedReason: newStatus === 'bloquee' ? reason : undefined
            }
          : t
      )
    );

    if (selectedTask && selectedTask.id === task.id) {
      setSelectedTask(prev =>
        prev
          ? {
              ...prev,
              status: newStatus,
              blockedReason: newStatus === 'bloquee' ? reason : undefined
            }
          : null
      );
    }

    // Journal d'audit
    const auditRecord: AuditRecord = {
      id: Date.now(),
      userId: currentUser.id,
      userName: `${currentUser.firstName} ${currentUser.lastName}`,
      userEmail: currentUser.email,
      action: 'TASK_STATUS_UPDATE',
      category: 'TASK',
      description: `Statut passé à [${newStatus}]${reason ? ` : ${reason}` : ''}`,
      targetInfo: `${task.projectCode} - ${task.title}`,
      ipAddress: '192.168.1.45',
      timestamp: 'À l\'instant'
    };
    setAuditLogs(prev => [auditRecord, ...prev]);

    // Générer notification pour les assignés
    task.assigneeIds.forEach(aid => {
      if (aid !== currentUser.id) {
        setNotifications(prev => [
          {
            id: Date.now() + aid,
            userId: aid,
            type: 'STATUS_CHANGE',
            title: `Mise à jour : ${task.projectCode}`,
            message: `La tâche a été passée à "${newStatus.replace('_', ' ')}" par ${currentUser.firstName}.`,
            taskId: task.id,
            isRead: false,
            createdAt: 'À l\'instant'
          },
          ...prev
        ]);
      }
    });

    showToast(`Tâche ${task.projectCode} mise à jour (${newStatus.replace('_', ' ')}).`);
  };

  // Action : création de tâche
  const handleCreateTask = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTaskTitle.trim()) return;

    const targetProject = projects.find(p => p.id === newTaskProjectId) || projects[0];
    const newId = 100 + tasks.length + 1;
    const taskCode = `${targetProject.codePrefix}-${String(newId).slice(-2)}`;

    const newTask: Task = {
      id: newId,
      projectId: targetProject.id,
      projectCode: taskCode,
      title: newTaskTitle.trim(),
      description: newTaskDesc.trim(),
      priority: newTaskPriority,
      status: 'a_faire',
      dueDate: newTaskDueDate,
      estimatedHours: newTaskHours,
      assigneeIds: newTaskAssigneeIds,
      createdAt: '2026-09-26'
    };

    setTasks(prev => [newTask, ...prev]);

    // Journal d'audit
    const auditRecord: AuditRecord = {
      id: Date.now(),
      userId: currentUser.id,
      userName: `${currentUser.firstName} ${currentUser.lastName}`,
      userEmail: currentUser.email,
      action: 'TASK_CREATE',
      category: 'TASK',
      description: `Création de tâche prioritaire [${newTaskPriority}]`,
      targetInfo: `${taskCode} - ${newTask.title}`,
      ipAddress: '192.168.1.45',
      timestamp: 'À l\'instant'
    };
    setAuditLogs(prev => [auditRecord, ...prev]);

    // Notification aux collaborateurs assignés
    newTaskAssigneeIds.forEach(aid => {
      setNotifications(prev => [
        {
          id: Date.now() + aid,
          userId: aid,
          type: 'ASSIGNMENT',
          title: `Nouvelle tâche assignée`,
          message: `${currentUser.firstName} vous a assigné à ${taskCode}.`,
          taskId: newTask.id,
          isRead: false,
          createdAt: 'À l\'instant'
        },
        ...prev
      ]);
    });

    setIsNewTaskModalOpen(false);
    setNewTaskTitle('');
    setNewTaskDesc('');
    showToast(`Tâche ${taskCode} créée avec succès.`);
  };

  // Action : création de projet
  const handleCreateProject = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newProjName.trim() || !newProjCode.trim()) return;

    const newProject: Project = {
      id: projects.length + 1,
      name: newProjName.trim(),
      codePrefix: newProjCode.trim().toUpperCase(),
      description: newProjDesc.trim(),
      ownerId: currentUser.id,
      ownerName: `${currentUser.firstName} ${currentUser.lastName}`,
      status: 'actif',
      startDate: '2026-09-26',
      dueDate: newProjDueDate,
      memberIds: [currentUser.id]
    };

    setProjects(prev => [...prev, newProject]);

    // Audit log
    const auditRecord: AuditRecord = {
      id: Date.now(),
      userId: currentUser.id,
      userName: `${currentUser.firstName} ${currentUser.lastName}`,
      userEmail: currentUser.email,
      action: 'PROJECT_CREATE',
      category: 'PROJECT',
      description: `Création du projet [${newProject.codePrefix}]`,
      targetInfo: newProject.name,
      ipAddress: '192.168.1.45',
      timestamp: 'À l\'instant'
    };
    setAuditLogs(prev => [auditRecord, ...prev]);

    setIsNewProjectModalOpen(false);
    setNewProjName('');
    setNewProjCode('');
    setNewProjDesc('');
    showToast(`Projet ${newProject.name} initialisé.`);
  };

  // Export CSV de l'audit log
  const handleExportCsv = () => {
    const headers = ['ID', 'Date', 'Collaborateur', 'Email', 'Action', 'Catégorie', 'Cible', 'Adresse IP', 'Description'];
    const rows = filteredAuditLogs.map(log => [
      log.id,
      log.timestamp,
      `"${log.userName}"`,
      log.userEmail,
      log.action,
      log.category,
      `"${log.targetInfo || ''}"`,
      log.ipAddress,
      `"${log.description.replace(/"/g, '""')}"`
    ]);

    const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `taskflow_audit_export_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('Export CSV téléchargé.');
  };

  // Copie de code
  const handleCopyCode = (content: string, key: string) => {
    navigator.clipboard.writeText(content);
    setCopiedKey(key);
    setTimeout(() => setCopiedKey(null), 2000);
  };

  // Métriques Dashboard
  const statusCounts = useMemo(() => ({
    a_faire: tasks.filter(t => t.status === 'a_faire').length,
    en_cours: tasks.filter(t => t.status === 'en_cours').length,
    en_revue: tasks.filter(t => t.status === 'en_revue').length,
    terminee: tasks.filter(t => t.status === 'terminee').length,
    bloquee: tasks.filter(t => t.status === 'bloquee').length
  }), [tasks]);

  const overdueTasks = useMemo(() => {
    return tasks.filter(t => t.dueDate < '2026-09-26' && t.status !== 'terminee');
  }, [tasks]);

  const workloadByMember = useMemo(() => {
    return members.map(m => {
      const activeCount = tasks.filter(
        t => t.assigneeIds.includes(m.id) && t.status !== 'terminee'
      ).length;
      return { member: m, activeCount };
    });
  }, [members, tasks]);

  // Filtrage des tâches
  const filteredTasks = useMemo(() => {
    return tasks.filter(t => {
      const matchSearch =
        t.title.toLowerCase().includes(globalSearch.toLowerCase()) ||
        t.description.toLowerCase().includes(globalSearch.toLowerCase()) ||
        t.projectCode.toLowerCase().includes(globalSearch.toLowerCase());

      const matchProj = filterProject === 'all' || t.projectId === filterProject;
      const matchStatus = filterStatus === 'all' || t.status === filterStatus;
      const matchPriority = filterPriority === 'all' || t.priority === filterPriority;
      const matchAssignee = filterAssignee === 'all' || t.assigneeIds.includes(filterAssignee as number);

      return matchSearch && matchProj && matchStatus && matchPriority && matchAssignee;
    });
  }, [tasks, globalSearch, filterProject, filterStatus, filterPriority, filterAssignee]);

  // Filtrage des logs d'audit
  const filteredAuditLogs = useMemo(() => {
    return auditLogs.filter(log => {
      const matchUser = auditUserFilter === 'all' || String(log.userId) === auditUserFilter;
      const matchCat = auditCategoryFilter === 'all' || log.category === auditCategoryFilter;
      const matchSearch =
        auditSearch === '' ||
        log.description.toLowerCase().includes(auditSearch.toLowerCase()) ||
        log.action.toLowerCase().includes(auditSearch.toLowerCase()) ||
        log.userName.toLowerCase().includes(auditSearch.toLowerCase());

      return matchUser && matchCat && matchSearch;
    });
  }, [auditLogs, auditUserFilter, auditCategoryFilter, auditSearch]);

  const selectedTaskComments = selectedTask ? comments.filter(c => c.taskId === selectedTask.id) : [];

  return (
    <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans antialiased selection:bg-slate-800 selection:text-white">
      {/* Toast Notification Subtil */}
      {toastMessage && (
        <div className="fixed bottom-5 right-5 z-50 bg-slate-900 text-white text-xs font-medium py-2.5 px-4 rounded-lg shadow-lg flex items-center gap-2 border border-slate-700 animate-in fade-in slide-in-from-bottom-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Barre de navigation principale (Header SaaS sobre) */}
      <header className="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
          <div className="flex items-center gap-8">
            {/* Logo */}
            <div className="flex items-center gap-2.5 cursor-pointer" onClick={() => setCurrentView('dashboard')}>
              <div className="w-8 h-8 rounded-md bg-slate-900 text-white flex items-center justify-center font-bold text-xs tracking-tight shadow-xs">
                TF
              </div>
              <div className="flex flex-col">
                <span className="text-sm font-semibold tracking-tight text-slate-900 leading-tight">
                  TaskFlow
                </span>
                <span className="text-[10px] text-slate-600 font-medium leading-none">
                  Espace Entreprise
                </span>
              </div>
            </div>

            {/* Onglets de navigation */}
            <nav className="hidden md:flex items-center gap-1">
              {[
                { id: 'dashboard', label: 'Tableau de bord', icon: LayoutDashboard },
                { id: 'projects', label: 'Projets', icon: FolderKanban },
                { id: 'tasks', label: 'Tâches & Suivi', icon: CheckSquare },
                { id: 'team', label: 'Équipe & Rôles', icon: Users },
                { id: 'audit', label: 'Journal d\'Audit', icon: Shield },
                { id: 'source', label: 'Architecture PHP', icon: FileCode }
              ].map(tab => {
                const Icon = tab.icon;
                const active = currentView === tab.id;
                return (
                  <button
                    key={tab.id}
                    onClick={() => setCurrentView(tab.id as any)}
                    className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors ${
                      active
                        ? 'bg-slate-100 text-slate-900 font-semibold'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                    }`}
                  >
                    <Icon className={`w-3.5 h-3.5 ${active ? 'text-slate-900' : 'text-slate-600'}`} />
                    <span>{tab.label}</span>
                  </button>
                );
              })}
            </nav>
          </div>

          {/* Côté Droit : Actions, Centre de notifications, Profil */}
          <div className="flex items-center gap-3">
            {/* Bouton Création Rapide */}
            <div className="hidden sm:flex items-center gap-1.5">
              <button
                onClick={() => setIsNewTaskModalOpen(true)}
                className="flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors shadow-xs"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Nouvelle tâche</span>
              </button>
            </div>

            <div className="h-4 w-px bg-slate-200 hidden sm:block"></div>

            {/* Centre de notifications */}
            <div className="relative">
              <button
                onClick={() => setIsNotifOpen(!isNotifOpen)}
                className="relative p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-md transition-colors"
                title="Centre de notifications"
              >
                <Bell className="w-4 h-4" />
                {unreadCount > 0 && (
                  <span className="absolute top-1 right-1 w-2 h-2 bg-rose-600 rounded-full ring-2 ring-white"></span>
                )}
              </button>

              {/* Popover Notifications */}
              {isNotifOpen && (
                <div className="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-slate-200 rounded-lg shadow-xl z-50 overflow-hidden text-xs animate-in fade-in zoom-in-95">
                  <div className="p-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                    <div className="flex items-center gap-2">
                      <span className="font-semibold text-slate-900">Notifications</span>
                      {unreadCount > 0 && (
                        <span className="bg-rose-100 text-rose-700 text-[10px] font-semibold px-1.5 py-0.5 rounded-full">
                          {unreadCount} non lue{unreadCount > 1 ? 's' : ''}
                        </span>
                      )}
                    </div>
                    {unreadCount > 0 && (
                      <button
                        onClick={handleMarkAllNotificationsRead}
                        className="text-[11px] text-slate-500 hover:text-slate-800 font-medium"
                      >
                        Tout marquer comme lu
                      </button>
                    )}
                  </div>

                  {/* Filtre d'affichage dans le menu */}
                  <div className="px-3 py-1.5 bg-white border-b border-slate-100 flex gap-2 text-[11px]">
                    <button
                      onClick={() => setNotifFilter('all')}
                      className={`pb-0.5 border-b-2 font-medium ${
                        notifFilter === 'all'
                          ? 'border-slate-900 text-slate-900'
                          : 'border-transparent text-slate-400 hover:text-slate-600'
                      }`}
                    >
                      Toutes
                    </button>
                    <button
                      onClick={() => setNotifFilter('unread')}
                      className={`pb-0.5 border-b-2 font-medium ${
                        notifFilter === 'unread'
                          ? 'border-slate-900 text-slate-900'
                          : 'border-transparent text-slate-400 hover:text-slate-600'
                      }`}
                    >
                      Non lues ({unreadCount})
                    </button>
                  </div>

                  {/* Liste des notifications */}
                  <div className="max-h-72 overflow-y-auto divide-y divide-slate-100">
                    {userNotifications.length === 0 ? (
                      <div className="p-6 text-center text-slate-400">
                        Aucune notification pour le moment.
                      </div>
                    ) : (
                      userNotifications.map(n => (
                        <div
                          key={n.id}
                          className={`p-3 transition-colors hover:bg-slate-50/80 flex items-start justify-between gap-2.5 ${
                            !n.isRead ? 'bg-slate-50/50' : ''
                          }`}
                        >
                          <div className="space-y-1 flex-1">
                            <div className="flex items-center gap-1.5">
                              {!n.isRead && (
                                <span className="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
                              )}
                              <span className="font-semibold text-slate-900">{n.title}</span>
                              <span className="text-[10px] text-slate-400">&bull; {n.createdAt}</span>
                            </div>
                            <p className="text-slate-600 text-[11px] leading-snug">{n.message}</p>
                          </div>
                          {!n.isRead && (
                            <button
                              onClick={() => handleMarkNotificationRead(n.id)}
                              className="text-[10px] text-slate-400 hover:text-slate-700 shrink-0 px-1 py-0.5 border border-slate-200 rounded bg-white"
                              title="Marquer comme lu"
                            >
                              Lu
                            </button>
                          )}
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}
            </div>

            <div className="h-4 w-px bg-slate-200"></div>

            {/* Sélecteur de Persona Collaborateur */}
            <div className="flex items-center gap-2">
              <div className="w-7 h-7 rounded-full bg-slate-200 border border-slate-300 text-slate-700 flex items-center justify-center font-semibold text-xs">
                {currentUser.firstName[0]}
                {currentUser.lastName[0]}
              </div>

              <select
                value={currentUser.id}
                onChange={e => {
                  const m = members.find(u => u.id === Number(e.target.value));
                  if (m) {
                    setCurrentUser(m);
                    showToast(`Session active : ${m.firstName} ${m.lastName} (${m.roleLabel}).`);
                  }
                }}
                className="text-xs font-medium text-slate-800 bg-transparent border-0 focus:ring-0 cursor-pointer pr-4"
              >
                {members.map(m => (
                  <option key={m.id} value={m.id}>
                    {m.firstName} {m.lastName} ({m.roleLabel})
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
      </header>

      {/* ============================================================================== */}
      {/* VUES PRINCIPALES */}
      {/* ============================================================================== */}
      <main className="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-7 flex-1">
        {/* ============================================================================== */}
        {/* VUE 1 : TABLEAU DE BORD (DASHBOARD) */}
        {/* ============================================================================== */}
        {currentView === 'dashboard' && (
          <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
              <div>
                <h1 className="text-xl font-bold text-slate-900 tracking-tight">Tableau de bord de pilotage</h1>
                <p className="text-xs text-slate-500 mt-0.5">
                  Indicateurs consolidés, suivi des retards critiques et répartition de la charge d'équipe.
                </p>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => setIsNewTaskModalOpen(true)}
                  className="px-3 py-1.5 text-xs font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors shadow-xs"
                >
                  Ajouter une tâche
                </button>
                <button
                  onClick={() => setIsNewProjectModalOpen(true)}
                  className="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition-colors shadow-xs"
                >
                  Nouveau projet
                </button>
              </div>
            </div>

            {/* Cartes métriques épurées */}
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
              <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs">
                <div className="text-[11px] font-medium text-slate-500">À faire</div>
                <div className="text-2xl font-bold text-slate-900 mt-1 tabular-nums">{statusCounts.a_faire}</div>
                <div className="text-[10px] text-slate-600 mt-1">Non entamées</div>
              </div>

              <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs">
                <div className="text-[11px] font-medium text-slate-500">En cours</div>
                <div className="text-2xl font-bold text-blue-600 mt-1 tabular-nums">{statusCounts.en_cours}</div>
                <div className="text-[10px] text-slate-600 mt-1">En production</div>
              </div>

              <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs">
                <div className="text-[11px] font-medium text-slate-500">En revue</div>
                <div className="text-2xl font-bold text-amber-600 mt-1 tabular-nums">{statusCounts.en_revue}</div>
                <div className="text-[10px] text-slate-600 mt-1">Validation & recette</div>
              </div>

              <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs">
                <div className="text-[11px] font-medium text-slate-500">Terminées</div>
                <div className="text-2xl font-bold text-emerald-600 mt-1 tabular-nums">{statusCounts.terminee}</div>
                <div className="text-[10px] text-slate-600 mt-1">Livrées</div>
              </div>

              <div className="bg-white border border-rose-200 rounded-lg p-3.5 shadow-xs">
                <div className="text-[11px] font-medium text-rose-700">Bloquées</div>
                <div className="text-2xl font-bold text-rose-600 mt-1 tabular-nums">{statusCounts.bloquee}</div>
                <div className="text-[10px] text-rose-600 mt-1">Motif documenté</div>
              </div>
            </div>

            {/* Ligne médiane : Retards critiques & Charge d'équipe */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Tâches en retard critique */}
              <div className="lg:col-span-2 bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                  <div className="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <div>
                      <h2 className="text-xs font-semibold text-slate-900 uppercase tracking-wide">
                        Tâches en retard critique
                      </h2>
                      <p className="text-[11px] text-slate-500">Échéance passée non clôturée</p>
                    </div>
                    <span className="text-[11px] font-semibold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded">
                      {overdueTasks.length} en retard
                    </span>
                  </div>

                  {overdueTasks.length === 0 ? (
                    <div className="p-8 text-center text-xs text-slate-400">
                      Toutes les tâches respectent le calendrier prévisionnel.
                    </div>
                  ) : (
                    <div className="divide-y divide-slate-100">
                      {overdueTasks.map(t => (
                        <div
                          key={t.id}
                          className="px-4 py-3 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition-colors"
                          onClick={() => setSelectedTask(t)}
                        >
                          <div className="space-y-1">
                            <div className="flex items-center gap-2">
                              <span className="font-mono text-[11px] font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">
                                {t.projectCode}
                              </span>
                              <span className="text-xs font-medium text-slate-900">{t.title}</span>
                            </div>
                            <div className="text-[11px] text-rose-600 flex items-center gap-1 font-medium">
                              <Clock className="w-3 h-3" />
                              <span>Échéance dépassée depuis le {t.dueDate}</span>
                            </div>
                          </div>
                          <span className="text-xs text-slate-400 flex items-center gap-1 hover:text-slate-600">
                            <span>Consulter</span>
                            <ChevronRight className="w-3 h-3" />
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                <div className="p-3 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between">
                  <span>Conforme à la règle métier d'escalade des retards</span>
                  <button onClick={() => setCurrentView('tasks')} className="text-slate-800 font-semibold hover:underline">
                    Gérer toutes les tâches &rarr;
                  </button>
                </div>
              </div>

              {/* Répartition de charge */}
              <div className="bg-white border border-slate-200 rounded-lg shadow-xs p-4 flex flex-col justify-between">
                <div>
                  <h2 className="text-xs font-semibold text-slate-900 uppercase tracking-wide mb-1">
                    Charge par collaborateur
                  </h2>
                  <p className="text-[11px] text-slate-500 mb-4">Volume de tâches actives assignées</p>

                  <div className="space-y-3">
                    {workloadByMember.map(({ member, activeCount }) => {
                      const percentage = Math.min(Math.round((activeCount / 6) * 100), 100);
                      return (
                        <div key={member.id} className="space-y-1">
                          <div className="flex justify-between text-xs">
                            <span className="font-medium text-slate-800">
                              {member.firstName} {member.lastName}
                            </span>
                            <span className="text-slate-500 font-mono text-[11px]">
                              {activeCount} active{activeCount > 1 ? 's' : ''}
                            </span>
                          </div>
                          <div className="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div
                              className={`h-full rounded-full ${
                                activeCount > 3 ? 'bg-amber-500' : 'bg-slate-800'
                              }`}
                              style={{ width: `${percentage}%` }}
                            ></div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>

                <div className="pt-4 border-t border-slate-100 text-[11px] text-slate-400 mt-4">
                  Quota moyen recommandé : &le; 4 tâches simultanées.
                </div>
              </div>
            </div>

            {/* Activité récente / Audit Log rapide */}
            <div className="bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden">
              <div className="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <div>
                  <h2 className="text-xs font-semibold text-slate-900 uppercase tracking-wide">
                    Dernières activités de l'équipe
                  </h2>
                  <p className="text-[11px] text-slate-500">Traçabilité complète des actions et modifications</p>
                </div>
                <button
                  onClick={() => setCurrentView('audit')}
                  className="text-xs text-slate-700 hover:text-slate-900 font-medium"
                >
                  Voir l'audit complet &rarr;
                </button>
              </div>

              <div className="divide-y divide-slate-100">
                {auditLogs.slice(0, 4).map(log => (
                  <div key={log.id} className="px-4 py-2.5 flex items-center justify-between text-xs">
                    <div className="flex items-center gap-3">
                      <span className="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                      <div>
                        <span className="font-medium text-slate-900">{log.userName}</span>
                        <span className="text-slate-500 ml-1.5">{log.description}</span>
                        {log.targetInfo && (
                          <span className="text-slate-400 font-mono text-[11px] ml-1">({log.targetInfo})</span>
                        )}
                      </div>
                    </div>
                    <div className="text-slate-400 text-[11px] font-mono shrink-0">{log.timestamp}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* ============================================================================== */}
        {/* VUE 2 : PROJETS */}
        {/* ============================================================================== */}
        {currentView === 'projects' && (
          <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
              <div>
                <h1 className="text-xl font-bold text-slate-900 tracking-tight">Projets</h1>
                <p className="text-xs text-slate-500 mt-0.5">
                  Gestion du portefeuille de projets, jalons prévisionnels et équipes dédiées.
                </p>
              </div>

              <button
                onClick={() => setIsNewProjectModalOpen(true)}
                className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors shadow-xs"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Nouveau projet</span>
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {projects.map(proj => {
                const projTasks = tasks.filter(t => t.projectId === proj.id);
                const completedTasks = projTasks.filter(t => t.status === 'terminee');
                const progress = projTasks.length > 0 ? Math.round((completedTasks.length / projTasks.length) * 100) : 0;

                return (
                  <div key={proj.id} className="bg-white border border-slate-200 rounded-lg p-5 shadow-xs flex flex-col justify-between">
                    <div>
                      <div className="flex items-start justify-between mb-3">
                        <div className="flex items-center gap-2">
                          <span className="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                            {proj.codePrefix}
                          </span>
                          <h3 className="text-sm font-semibold text-slate-900">{proj.name}</h3>
                        </div>
                        <span className="text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded capitalize">
                          {proj.status}
                        </span>
                      </div>

                      <p className="text-xs text-slate-600 line-clamp-2 mb-4 leading-relaxed">
                        {proj.description}
                      </p>

                      <div className="space-y-1 mb-4">
                        <div className="flex justify-between text-[11px] text-slate-500">
                          <span>Progression</span>
                          <span className="font-mono font-medium text-slate-700">{progress}% ({completedTasks.length}/{projTasks.length} tâches)</span>
                        </div>
                        <div className="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                          <div className="h-full bg-slate-900 rounded-full" style={{ width: `${progress}%` }}></div>
                        </div>
                      </div>
                    </div>

                    <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                      <div className="flex items-center gap-1.5">
                        <span>Responsable :</span>
                        <span className="font-medium text-slate-800">{proj.ownerName}</span>
                      </div>
                      <div className="flex items-center gap-1 font-mono text-[11px]">
                        <Calendar className="w-3.5 h-3.5 text-slate-400" />
                        <span>Échéance : {proj.dueDate}</span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* ============================================================================== */}
        {/* VUE 3 : TÂCHES & SUIVI (TABLE / KANBAN) */}
        {/* ============================================================================== */}
        {currentView === 'tasks' && (
          <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
              <div>
                <h1 className="text-xl font-bold text-slate-900 tracking-tight">Tâches & Suivi d'avancement</h1>
                <p className="text-xs text-slate-500 mt-0.5">
                  Filtrage multi-critères, vue tabulaire ou tableau Kanban interactif.
                </p>
              </div>

              <div className="flex items-center gap-2">
                <div className="flex items-center bg-slate-100 p-0.5 rounded-md border border-slate-200">
                  <button
                    onClick={() => setTaskViewMode('table')}
                    className={`flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium transition-colors ${
                      taskViewMode === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
                    }`}
                  >
                    <TableIcon className="w-3 h-3" />
                    <span>Table</span>
                  </button>
                  <button
                    onClick={() => setTaskViewMode('kanban')}
                    className={`flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium transition-colors ${
                      taskViewMode === 'kanban' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
                    }`}
                  >
                    <Kanban className="w-3 h-3" />
                    <span>Kanban</span>
                  </button>
                </div>

                <button
                  onClick={() => setIsNewTaskModalOpen(true)}
                  className="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors shadow-xs"
                >
                  <Plus className="w-3.5 h-3.5" />
                  <span>Nouvelle tâche</span>
                </button>
              </div>
            </div>

            {/* Barre de recherche et filtres */}
            <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs space-y-3">
              <div className="flex flex-col sm:flex-row gap-3">
                <div className="relative flex-1">
                  <Search className="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Rechercher par titre, code ou mot-clé..."
                    value={globalSearch}
                    onChange={e => setGlobalSearch(e.target.value)}
                    className="w-full pl-9 pr-3 py-1.5 text-xs border border-slate-200 rounded-md focus:outline-none focus:border-slate-400"
                  />
                </div>

                <div className="flex flex-wrap items-center gap-2 text-xs">
                  <select
                    value={filterProject}
                    onChange={e => setFilterProject(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                    className="border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 bg-white focus:outline-none"
                  >
                    <option value="all">Tous les projets</option>
                    {projects.map(p => (
                      <option key={p.id} value={p.id}>{p.codePrefix} - {p.name}</option>
                    ))}
                  </select>

                  <select
                    value={filterStatus}
                    onChange={e => setFilterStatus(e.target.value)}
                    className="border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 bg-white focus:outline-none"
                  >
                    <option value="all">Tous les statuts</option>
                    <option value="a_faire">À faire</option>
                    <option value="en_cours">En cours</option>
                    <option value="en_revue">En revue</option>
                    <option value="terminee">Terminée</option>
                    <option value="bloquee">Bloquée</option>
                  </select>

                  <select
                    value={filterPriority}
                    onChange={e => setFilterPriority(e.target.value)}
                    className="border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 bg-white focus:outline-none"
                  >
                    <option value="all">Toutes priorités</option>
                    <option value="basse">Basse</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="haute">Haute</option>
                    <option value="urgente">Urgente</option>
                  </select>

                  <select
                    value={filterAssignee}
                    onChange={e => setFilterAssignee(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                    className="border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 bg-white focus:outline-none"
                  >
                    <option value="all">Tous les assignés</option>
                    {members.map(m => (
                      <option key={m.id} value={m.id}>{m.firstName} {m.lastName}</option>
                    ))}
                  </select>

                  {(globalSearch || filterProject !== 'all' || filterStatus !== 'all' || filterPriority !== 'all' || filterAssignee !== 'all') && (
                    <button
                      onClick={() => {
                        setGlobalSearch('');
                        setFilterProject('all');
                        setFilterStatus('all');
                        setFilterPriority('all');
                        setFilterAssignee('all');
                      }}
                      className="text-slate-500 hover:text-slate-800 text-xs px-2 py-1 underline"
                    >
                      Réinitialiser
                    </button>
                  )}
                </div>
              </div>
            </div>

            {/* Affichage Mode Tableau */}
            {taskViewMode === 'table' && (
              <div className="bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden">
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                      <tr>
                        <th className="px-4 py-3">Code</th>
                        <th className="px-4 py-3">Titre de la tâche</th>
                        <th className="px-4 py-3">Priorité</th>
                        <th className="px-4 py-3">Statut</th>
                        <th className="px-4 py-3">Assignés</th>
                        <th className="px-4 py-3">Échéance</th>
                        <th className="px-4 py-3 text-right">Actions</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {filteredTasks.length === 0 ? (
                        <tr>
                          <td colSpan={7} className="px-4 py-8 text-center text-slate-400">
                            Aucune tâche ne correspond aux critères sélectionnés.
                          </td>
                        </tr>
                      ) : (
                        filteredTasks.map(task => (
                          <tr
                            key={task.id}
                            className="hover:bg-slate-50/80 transition-colors cursor-pointer"
                            onClick={() => setSelectedTask(task)}
                          >
                            <td className="px-4 py-3 font-mono font-semibold text-slate-700 whitespace-nowrap">
                              {task.projectCode}
                            </td>
                            <td className="px-4 py-3 font-medium text-slate-900 max-w-xs truncate">
                              {task.title}
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap">
                              <span
                                className={`text-[10px] font-semibold px-2 py-0.5 rounded capitalize ${
                                  task.priority === 'urgente'
                                    ? 'bg-rose-50 text-rose-700 border border-rose-200'
                                    : task.priority === 'haute'
                                    ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                    : 'bg-slate-100 text-slate-700'
                                }`}
                              >
                                {task.priority}
                              </span>
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap" onClick={e => e.stopPropagation()}>
                              <select
                                value={task.status}
                                onChange={e => handleStatusChange(task, e.target.value as any)}
                                className="text-xs bg-slate-50 border border-slate-200 rounded px-2 py-1 text-slate-700 focus:outline-none focus:border-slate-400 cursor-pointer capitalize"
                              >
                                <option value="a_faire">À faire</option>
                                <option value="en_cours">En cours</option>
                                <option value="en_revue">En revue</option>
                                <option value="terminee">Terminée</option>
                                <option value="bloquee">Bloquée</option>
                              </select>
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap">
                              <div className="flex -space-x-1">
                                {members
                                  .filter(m => task.assigneeIds.includes(m.id))
                                  .map(am => (
                                    <div
                                      key={am.id}
                                      title={`${am.firstName} ${am.lastName}`}
                                      className="w-5 h-5 rounded-full bg-slate-200 border border-white text-[10px] flex items-center justify-center font-bold text-slate-700"
                                    >
                                      {am.firstName[0]}
                                    </div>
                                  ))}
                              </div>
                            </td>
                            <td className="px-4 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                              {task.dueDate}
                            </td>
                            <td className="px-4 py-3 text-right whitespace-nowrap" onClick={e => e.stopPropagation()}>
                              <button
                                onClick={() => setSelectedTask(task)}
                                className="text-slate-600 hover:text-slate-900 font-medium text-[11px] px-2 py-1 rounded hover:bg-slate-100"
                              >
                                Détails &rarr;
                              </button>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* Affichage Mode Kanban */}
            {taskViewMode === 'kanban' && (
              <div className="grid grid-cols-1 md:grid-cols-5 gap-3.5">
                {[
                  { id: 'a_faire', label: 'À faire', color: 'border-slate-300' },
                  { id: 'en_cours', label: 'En cours', color: 'border-blue-400' },
                  { id: 'en_revue', label: 'En revue', color: 'border-amber-400' },
                  { id: 'terminee', label: 'Terminée', color: 'border-emerald-400' },
                  { id: 'bloquee', label: 'Bloquée', color: 'border-rose-400' }
                ].map(col => {
                  const colTasks = filteredTasks.filter(t => t.status === col.id);
                  return (
                    <div key={col.id} className="bg-slate-100/70 rounded-lg p-3 flex flex-col min-h-[420px]">
                      <div className="flex items-center justify-between mb-3 pb-2 border-b border-slate-200">
                        <span className="font-semibold text-xs text-slate-800">{col.label}</span>
                        <span className="text-[11px] font-mono font-medium text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">
                          {colTasks.length}
                        </span>
                      </div>

                      <div className="space-y-2.5 flex-1">
                        {colTasks.map(t => (
                          <div
                            key={t.id}
                            onClick={() => setSelectedTask(t)}
                            className="bg-white border border-slate-200 rounded-md p-3 shadow-xs hover:border-slate-400 transition-colors cursor-pointer space-y-2"
                          >
                            <div className="flex items-center justify-between">
                              <span className="font-mono text-[10px] font-bold text-slate-600">
                                {t.projectCode}
                              </span>
                              <span
                                className={`text-[9px] font-semibold px-1.5 py-0.2 rounded capitalize ${
                                  t.priority === 'urgente'
                                    ? 'bg-rose-50 text-rose-700'
                                    : t.priority === 'haute'
                                    ? 'bg-amber-50 text-amber-700'
                                    : 'bg-slate-100 text-slate-600'
                                }`}
                              >
                                {t.priority}
                              </span>
                            </div>

                            <h4 className="text-xs font-semibold text-slate-900 leading-snug line-clamp-2">
                              {t.title}
                            </h4>

                            {t.status === 'bloquee' && t.blockedReason && (
                              <p className="text-[10px] text-rose-600 bg-rose-50 p-1.5 rounded border border-rose-100 line-clamp-2">
                                {t.blockedReason}
                              </p>
                            )}

                            <div className="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                              <span>{t.dueDate}</span>
                              <div className="flex -space-x-1">
                                {members
                                  .filter(m => t.assigneeIds.includes(m.id))
                                  .map(am => (
                                    <div
                                      key={am.id}
                                      className="w-4 h-4 rounded-full bg-slate-200 text-[8px] flex items-center justify-center font-bold text-slate-700 border border-white"
                                    >
                                      {am.firstName[0]}
                                    </div>
                                  ))}
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        )}

        {/* ============================================================================== */}
        {/* VUE 4 : ÉQUIPE & RÔLES */}
        {/* ============================================================================== */}
        {currentView === 'team' && (
          <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
              <div>
                <h1 className="text-xl font-bold text-slate-900 tracking-tight">Équipe & Gestion des Rôles (RBAC)</h1>
                <p className="text-xs text-slate-500 mt-0.5">
                  Permissions d'accès, départements et affectation des privilèges administrateur / manager / membre.
                </p>
              </div>
            </div>

            <div className="bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                  <tr>
                    <th className="px-4 py-3">Collaborateur</th>
                    <th className="px-4 py-3">Rôle actuel</th>
                    <th className="px-4 py-3">Département</th>
                    <th className="px-4 py-3">Dernière activité</th>
                    <th className="px-4 py-3 text-right">Modifier le rôle</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {members.map(m => (
                    <tr key={m.id} className="hover:bg-slate-50/80">
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2.5">
                          <div className="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                            {m.firstName[0]}
                            {m.lastName[0]}
                          </div>
                          <div>
                            <div className="font-semibold text-slate-900">{m.firstName} {m.lastName}</div>
                            <div className="text-[11px] text-slate-500">{m.email}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-4 py-3">
                        <span className="text-[11px] font-medium bg-slate-100 text-slate-800 px-2 py-0.5 rounded">
                          {m.roleLabel}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-slate-600">{m.department}</td>
                      <td className="px-4 py-3 font-mono text-[11px] text-slate-500">{m.lastLogin}</td>
                      <td className="px-4 py-3 text-right">
                        {currentUser.roleId === 1 ? (
                          <select
                            value={m.roleId}
                            onChange={e => {
                              const newRoleId = Number(e.target.value);
                              const roleLabels: Record<number, string> = { 1: 'Administrateur', 2: 'Chef de projet', 3: 'Membre' };
                              const roleNames: Record<number, any> = { 1: 'admin', 2: 'manager', 3: 'member' };
                              setMembers(prev =>
                                prev.map(u => (u.id === m.id ? { ...u, roleId: newRoleId, roleLabel: roleLabels[newRoleId], roleName: roleNames[newRoleId] } : u))
                              );
                              const auditRecord: AuditRecord = {
                                id: Date.now(),
                                userId: currentUser.id,
                                userName: `${currentUser.firstName} ${currentUser.lastName}`,
                                userEmail: currentUser.email,
                                action: 'ROLE_UPDATE',
                                category: 'RBAC',
                                description: `Rôle de ${m.firstName} ${m.lastName} modifié en [${roleLabels[newRoleId]}]`,
                                targetInfo: m.email,
                                ipAddress: '192.168.1.45',
                                timestamp: 'À l\'instant'
                              };
                              setAuditLogs(prev => [auditRecord, ...prev]);
                              showToast(`Rôle de ${m.firstName} mis à jour.`);
                            }}
                            className="text-xs bg-slate-50 border border-slate-200 rounded px-2 py-1 text-slate-700 focus:outline-none"
                          >
                            <option value={1}>Administrateur</option>
                            <option value={2}>Chef de projet</option>
                            <option value={3}>Membre</option>
                          </select>
                        ) : (
                          <span className="text-[11px] text-slate-400 italic">Réservé Admin</span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* ============================================================================== */}
        {/* VUE 5 : JOURNAL D'AUDIT (LOT 4 COMPLET) */}
        {/* ============================================================================== */}
        {currentView === 'audit' && (
          <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
              <div>
                <h1 className="text-xl font-bold text-slate-900 tracking-tight">Journal d'Audit & Traçabilité (Lot 4)</h1>
                <p className="text-xs text-slate-500 mt-0.5">
                  Registre immuable des accès, authentifications, changements de rôles et modifications opérationnelles.
                </p>
              </div>

              <button
                onClick={handleExportCsv}
                className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition-colors shadow-xs"
              >
                <Download className="w-3.5 h-3.5" />
                <span>Exporter en CSV</span>
              </button>
            </div>

            {/* Filtres de recherche du journal d'audit */}
            <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs flex flex-wrap items-center gap-3 text-xs">
              <div className="relative flex-1 min-w-[200px]">
                <Search className="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                <input
                  type="text"
                  placeholder="Rechercher une action, un utilisateur ou une cible..."
                  value={auditSearch}
                  onChange={e => setAuditSearch(e.target.value)}
                  className="w-full pl-9 pr-3 py-1.5 text-xs border border-slate-200 rounded-md focus:outline-none"
                />
              </div>

              <select
                value={auditCategoryFilter}
                onChange={e => setAuditCategoryFilter(e.target.value)}
                className="border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 bg-white focus:outline-none"
              >
                <option value="all">Toutes les catégories</option>
                <option value="AUTH">Authentification (AUTH)</option>
                <option value="RBAC">Rôles & Accès (RBAC)</option>
                <option value="PROJECT">Projets (PROJECT)</option>
                <option value="TASK">Tâches (TASK)</option>
                <option value="COMMENT">Commentaires (COMMENT)</option>
              </select>

              <select
                value={auditUserFilter}
                onChange={e => setAuditUserFilter(e.target.value)}
                className="border border-slate-200 rounded-md px-2.5 py-1.5 text-slate-700 bg-white focus:outline-none"
              >
                <option value="all">Tous les collaborateurs</option>
                {members.map(m => (
                  <option key={m.id} value={String(m.id)}>
                    {m.firstName} {m.lastName}
                  </option>
                ))}
              </select>
            </div>

            {/* Table des enregistrements d'audit */}
            <div className="bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                      <th className="px-4 py-3">Horodatage</th>
                      <th className="px-4 py-3">Collaborateur</th>
                      <th className="px-4 py-3">Catégorie</th>
                      <th className="px-4 py-3">Action</th>
                      <th className="px-4 py-3">Description / Contexte</th>
                      <th className="px-4 py-3">Adresse IP</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {filteredAuditLogs.length === 0 ? (
                      <tr>
                        <td colSpan={6} className="px-4 py-8 text-center text-slate-400">
                          Aucun événement ne correspond aux critères de filtre.
                        </td>
                      </tr>
                    ) : (
                      filteredAuditLogs.map(record => (
                        <tr key={record.id} className="hover:bg-slate-50/80">
                          <td className="px-4 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {record.timestamp}
                          </td>
                          <td className="px-4 py-3 whitespace-nowrap">
                            <span className="font-semibold text-slate-900">{record.userName}</span>
                            <span className="text-[11px] text-slate-400 block">{record.userEmail}</span>
                          </td>
                          <td className="px-4 py-3 whitespace-nowrap">
                            <span className="text-[10px] font-mono font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                              {record.category}
                            </span>
                          </td>
                          <td className="px-4 py-3 font-mono font-semibold text-slate-800 text-[11px] whitespace-nowrap">
                            {record.action}
                          </td>
                          <td className="px-4 py-3 text-slate-700">
                            <div>{record.description}</div>
                            {record.targetInfo && (
                              <div className="text-[10px] text-slate-400 font-mono mt-0.5">
                                Cible : {record.targetInfo}
                              </div>
                            )}
                          </td>
                          <td className="px-4 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {record.ipAddress}
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* ============================================================================== */}
        {/* VUE 6 : EXPLORATEUR DE CODE SOURCE & ARCHITECTURE PHP */}
        {/* ============================================================================== */}
        {currentView === 'source' && (
          <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
              <div>
                <h1 className="text-xl font-bold text-slate-900 tracking-tight">Architecture Backend PHP 8.2 & MySQL</h1>
                <p className="text-xs text-slate-500 mt-0.5">
                  Consultez et copiez l'implémentation native des modèles, contrôleurs et scripts DDL.
                </p>
              </div>

              <div className="flex items-center gap-2">
                <a
                  href="/taskflow_project.zip"
                  download="taskflow_project.zip"
                  className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors shadow-xs"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>Télécharger le code source (.ZIP)</span>
                </a>
                <button
                  onClick={() => handleCopyCode(PHP_FILES[selectedFileKey].content, selectedFileKey)}
                  className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition-colors shadow-xs"
                >
                  {copiedKey === selectedFileKey ? (
                    <>
                      <Check className="w-3.5 h-3.5 text-emerald-600" />
                      <span>Copié</span>
                    </>
                  ) : (
                    <>
                      <Copy className="w-3.5 h-3.5" />
                      <span>Copier le fichier</span>
                    </>
                  )}
                </button>
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
              <div className="lg:col-span-1 bg-white border border-slate-200 rounded-lg p-2 space-y-1 shadow-xs">
                <div className="text-[10px] font-semibold text-slate-400 uppercase tracking-wider px-3 py-2">
                  Fichiers Backend
                </div>
                {Object.keys(PHP_FILES).map(k => (
                  <button
                    key={k}
                    onClick={() => setSelectedFileKey(k)}
                    className={`w-full text-left px-3 py-2 rounded text-xs font-mono transition-colors flex items-center justify-between ${
                      selectedFileKey === k
                        ? 'bg-slate-900 text-white font-medium'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                    }`}
                  >
                    <span className="truncate">{k}</span>
                  </button>
                ))}
              </div>

              <div className="lg:col-span-3 bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden flex flex-col">
                <div className="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center justify-between">
                  <div>
                    <div className="font-mono text-xs font-semibold text-slate-900">
                      {PHP_FILES[selectedFileKey].path}
                    </div>
                    <div className="text-[11px] text-slate-500 mt-0.5">
                      {PHP_FILES[selectedFileKey].desc}
                    </div>
                  </div>
                  <span className="text-[10px] font-mono px-2 py-0.5 bg-slate-200 text-slate-700 rounded">
                    {PHP_FILES[selectedFileKey].type}
                  </span>
                </div>

                <div className="p-4 bg-slate-900 text-slate-100 font-mono text-xs overflow-x-auto max-h-[500px] leading-relaxed">
                  <pre className="whitespace-pre">
                    {PHP_FILES[selectedFileKey].content}
                  </pre>
                </div>
              </div>
            </div>
          </div>
        )}
      </main>

      {/* ============================================================================== */}
      {/* MODALE : DÉTAIL D'UNE TÂCHE & FIL DE COMMENTAIRES */}
      {/* ============================================================================== */}
      {selectedTask && (
        <div className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-lg border border-slate-200 shadow-xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden text-xs">
            {/* Header */}
            <div className="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/60">
              <div className="flex items-center gap-2">
                <span className="font-mono text-xs font-bold text-slate-800 bg-white border border-slate-200 px-2 py-0.5 rounded">
                  {selectedTask.projectCode}
                </span>
                <span className="text-xs font-medium text-slate-500">
                  {projects.find(p => p.id === selectedTask.projectId)?.name}
                </span>
              </div>
              <button
                onClick={() => setSelectedTask(null)}
                className="text-slate-400 hover:text-slate-600 p-1 rounded"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Corps défilable */}
            <div className="p-5 overflow-y-auto space-y-5 flex-1">
              <div>
                <h2 className="text-base font-semibold text-slate-900 mb-2">
                  {selectedTask.title}
                </h2>
                <p className="text-slate-600 leading-relaxed bg-slate-50 p-3 rounded border border-slate-100">
                  {selectedTask.description || 'Aucune description détaillée.'}
                </p>
              </div>

              {selectedTask.status === 'bloquee' && selectedTask.blockedReason && (
                <div className="p-3 bg-rose-50 border border-rose-200 rounded text-rose-700">
                  <div className="font-semibold mb-0.5">Motif de blocage documenté :</div>
                  <div>{selectedTask.blockedReason}</div>
                </div>
              )}

              {/* Métadonnées */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 border-y border-slate-100 py-3 text-[11px]">
                <div>
                  <span className="text-slate-400 block">Statut</span>
                  <select
                    value={selectedTask.status}
                    onChange={e => handleStatusChange(selectedTask, e.target.value as any)}
                    className="font-semibold text-slate-800 bg-transparent border-0 focus:ring-0 p-0 cursor-pointer capitalize"
                  >
                    <option value="a_faire">À faire</option>
                    <option value="en_cours">En cours</option>
                    <option value="en_revue">En revue</option>
                    <option value="terminee">Terminée</option>
                    <option value="bloquee">Bloquée</option>
                  </select>
                </div>
                <div>
                  <span className="text-slate-400 block">Priorité</span>
                  <span className="font-semibold text-slate-800 capitalize">
                    {selectedTask.priority}
                  </span>
                </div>
                <div>
                  <span className="text-slate-400 block">Échéance</span>
                  <span className="font-mono font-semibold text-slate-800">
                    {selectedTask.dueDate}
                  </span>
                </div>
                <div>
                  <span className="text-slate-400 block">Estimation</span>
                  <span className="font-semibold text-slate-800 tabular-nums">
                    {selectedTask.estimatedHours} h
                  </span>
                </div>
              </div>

              {/* Collaborateurs assignés */}
              <div>
                <span className="text-slate-500 font-medium block mb-1.5">Collaborateurs assignés</span>
                <div className="flex flex-wrap gap-1.5">
                  {members.filter(m => selectedTask.assigneeIds.includes(m.id)).map(am => (
                    <span key={am.id} className="px-2 py-1 rounded bg-slate-100 text-slate-700 font-medium text-[11px]">
                      {am.firstName} {am.lastName} ({am.department})
                    </span>
                  ))}
                </div>
              </div>

              {/* Fil de discussion / Commentaires */}
              <div className="space-y-3 pt-2">
                <div className="flex items-center justify-between border-b border-slate-100 pb-2">
                  <span className="font-semibold text-slate-900 flex items-center gap-1.5">
                    <MessageSquare className="w-3.5 h-3.5 text-slate-400" />
                    Fil des commentaires ({selectedTaskComments.length})
                  </span>
                </div>

                {selectedTaskComments.length === 0 ? (
                  <p className="text-slate-400 italic py-2">
                    Aucun message pour l'instant. Ajoutez une note ci-dessous.
                  </p>
                ) : (
                  <div className="space-y-2">
                    {selectedTaskComments.map(comment => (
                      <div key={comment.id} className="p-3 rounded border border-slate-200 bg-slate-50/60 space-y-1">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-2">
                            <span className="font-semibold text-slate-900">{comment.userName}</span>
                            <span className="text-[10px] text-slate-400">({comment.userRole}) &bull; {comment.timestamp}</span>
                          </div>
                          {(comment.userId === currentUser.id || currentUser.roleId === 1) && (
                            <button
                              onClick={() => {
                                setComments(prev => prev.filter(c => c.id !== comment.id));
                                showToast('Commentaire supprimé.');
                              }}
                              className="text-slate-400 hover:text-rose-600 p-0.5"
                              title="Supprimer"
                            >
                              <Trash2 className="w-3 h-3" />
                            </button>
                          )}
                        </div>
                        <p className="text-slate-700 leading-relaxed whitespace-pre-wrap">{comment.content}</p>
                      </div>
                    ))}
                  </div>
                )}

                {/* Saisie d'un nouveau commentaire */}
                <form onSubmit={handleAddComment} className="pt-2">
                  <div className="flex gap-2">
                    <textarea
                      rows={2}
                      value={newCommentText}
                      onChange={e => setNewCommentText(e.target.value)}
                      placeholder="Ajouter une remarque, question ou compte-rendu..."
                      className="flex-1 border border-slate-200 rounded p-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400"
                    />
                    <button
                      type="submit"
                      className="px-3 bg-slate-900 hover:bg-slate-800 text-white rounded font-medium text-xs flex items-center justify-center gap-1 shrink-0"
                    >
                      <Send className="w-3.5 h-3.5" />
                      <span>Publier</span>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* ============================================================================== */}
      {/* MODALE : MOTIF DE BLOCAGE REQUIS */}
      {/* ============================================================================== */}
      {blockingTask && (
        <div className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-5 space-y-4 text-xs">
            <div>
              <h3 className="text-sm font-semibold text-slate-900 flex items-center gap-2">
                <AlertTriangle className="w-4 h-4 text-rose-600" />
                <span>Justification de blocage requise</span>
              </h3>
              <p className="text-slate-500 mt-1">
                Conformément aux règles métier, le passage au statut "Bloquée" nécessite un motif précis pour alerter le chef de projet.
              </p>
            </div>

            <textarea
              rows={3}
              value={blockReasonInput}
              onChange={e => setBlockReasonInput(e.target.value)}
              placeholder="Exemple : En attente de validation comptable / Dépendance externe..."
              className="w-full border border-slate-200 rounded p-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400"
            />

            <div className="flex justify-end gap-2">
              <button
                type="button"
                onClick={() => setBlockingTask(null)}
                className="px-3 py-1.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50"
              >
                Annuler
              </button>
              <button
                type="button"
                disabled={!blockReasonInput.trim()}
                onClick={() => {
                  applyStatusUpdate(blockingTask, 'bloquee', blockReasonInput.trim());
                  setBlockingTask(null);
                }}
                className="px-3 py-1.5 rounded bg-rose-600 text-white font-medium hover:bg-rose-700 disabled:opacity-50"
              >
                Confirmer le blocage
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ============================================================================== */}
      {/* MODALE : CRÉATION DE TÂCHE */}
      {/* ============================================================================== */}
      {isNewTaskModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-lg border border-slate-200 shadow-xl max-w-lg w-full p-5 space-y-4 text-xs">
            <div className="flex items-center justify-between border-b border-slate-100 pb-3">
              <h3 className="text-sm font-semibold text-slate-900">Nouvelle tâche</h3>
              <button onClick={() => setIsNewTaskModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleCreateTask} className="space-y-3.5">
              <div>
                <label className="block text-slate-600 font-medium mb-1">Titre de la tâche *</label>
                <input
                  type="text"
                  required
                  value={newTaskTitle}
                  onChange={e => setNewTaskTitle(e.target.value)}
                  placeholder="Ex : Rédaction des spécifications fonctionnelles..."
                  className="w-full border border-slate-200 rounded px-2.5 py-1.5 focus:outline-none focus:border-slate-400 text-xs"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-600 font-medium mb-1">Projet de rattachement</label>
                  <select
                    value={newTaskProjectId}
                    onChange={e => setNewTaskProjectId(Number(e.target.value))}
                    className="w-full border border-slate-200 rounded px-2 py-1.5 focus:outline-none text-xs bg-white"
                  >
                    {projects.map(p => (
                      <option key={p.id} value={p.id}>{p.codePrefix} - {p.name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-slate-600 font-medium mb-1">Priorité</label>
                  <select
                    value={newTaskPriority}
                    onChange={e => setNewTaskPriority(e.target.value as any)}
                    className="w-full border border-slate-200 rounded px-2 py-1.5 focus:outline-none text-xs bg-white"
                  >
                    <option value="basse">Basse</option>
                    <option value="moyenne">Moyenne</option>
                    <option value="haute">Haute</option>
                    <option value="urgente">Urgente</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-600 font-medium mb-1">Date d'échéance</label>
                  <input
                    type="date"
                    value={newTaskDueDate}
                    onChange={e => setNewTaskDueDate(e.target.value)}
                    className="w-full border border-slate-200 rounded px-2.5 py-1.5 focus:outline-none text-xs"
                  />
                </div>
                <div>
                  <label className="block text-slate-600 font-medium mb-1">Estimation (heures)</label>
                  <input
                    type="number"
                    step="0.5"
                    value={newTaskHours}
                    onChange={e => setNewTaskHours(Number(e.target.value))}
                    className="w-full border border-slate-200 rounded px-2.5 py-1.5 focus:outline-none text-xs"
                  />
                </div>
              </div>

              <div>
                <label className="block text-slate-600 font-medium mb-1">Collaborateur assigné</label>
                <select
                  value={newTaskAssigneeIds[0] || 3}
                  onChange={e => setNewTaskAssigneeIds([Number(e.target.value)])}
                  className="w-full border border-slate-200 rounded px-2 py-1.5 focus:outline-none text-xs bg-white"
                >
                  {members.map(m => (
                    <option key={m.id} value={m.id}>{m.firstName} {m.lastName} ({m.department})</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-slate-600 font-medium mb-1">Description</label>
                <textarea
                  rows={2}
                  value={newTaskDesc}
                  onChange={e => setNewTaskDesc(e.target.value)}
                  placeholder="Contexte et critères d'acceptation..."
                  className="w-full border border-slate-200 rounded p-2 focus:outline-none text-xs"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsNewTaskModalOpen(false)}
                  className="px-3 py-1.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  className="px-3.5 py-1.5 rounded bg-slate-900 text-white font-medium hover:bg-slate-800"
                >
                  Créer la tâche
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ============================================================================== */}
      {/* MODALE : CRÉATION DE PROJET */}
      {/* ============================================================================== */}
      {isNewProjectModalOpen && (
        <div className="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-5 space-y-4 text-xs">
            <div className="flex items-center justify-between border-b border-slate-100 pb-3">
              <h3 className="text-sm font-semibold text-slate-900">Nouveau projet</h3>
              <button onClick={() => setIsNewProjectModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleCreateProject} className="space-y-3.5">
              <div>
                <label className="block text-slate-600 font-medium mb-1">Nom du projet *</label>
                <input
                  type="text"
                  required
                  value={newProjName}
                  onChange={e => setNewProjName(e.target.value)}
                  placeholder="Ex : Migration Plateforme CRM"
                  className="w-full border border-slate-200 rounded px-2.5 py-1.5 focus:outline-none text-xs"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-600 font-medium mb-1">Code préfixe (ex: CRM) *</label>
                  <input
                    type="text"
                    required
                    maxLength={8}
                    value={newProjCode}
                    onChange={e => setNewProjCode(e.target.value.toUpperCase())}
                    placeholder="CRM"
                    className="w-full border border-slate-200 rounded px-2.5 py-1.5 focus:outline-none font-mono text-xs"
                  />
                </div>
                <div>
                  <label className="block text-slate-600 font-medium mb-1">Échéance cible</label>
                  <input
                    type="date"
                    value={newProjDueDate}
                    onChange={e => setNewProjDueDate(e.target.value)}
                    className="w-full border border-slate-200 rounded px-2.5 py-1.5 focus:outline-none text-xs"
                  />
                </div>
              </div>

              <div>
                <label className="block text-slate-600 font-medium mb-1">Description succincte</label>
                <textarea
                  rows={2}
                  value={newProjDesc}
                  onChange={e => setNewProjDesc(e.target.value)}
                  placeholder="Objectifs et livrables majeurs du projet..."
                  className="w-full border border-slate-200 rounded p-2 focus:outline-none text-xs"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsNewProjectModalOpen(false)}
                  className="px-3 py-1.5 rounded border border-slate-200 text-slate-600 hover:bg-slate-50"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  className="px-3.5 py-1.5 rounded bg-slate-900 text-white font-medium hover:bg-slate-800"
                >
                  Créer le projet
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Footer épuré SaaS */}
      <footer className="bg-white border-t border-slate-200 py-3 mt-auto">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
          <div>
            <span className="font-semibold text-slate-700">TaskFlow Enterprise</span> &bull; Lot 4 opérationnel &bull; Notifications &amp; Journal d'Audit
          </div>
          <div className="flex items-center gap-4 text-[11px]">
            <span>PHP 8.2 &bull; MySQL / MariaDB &bull; Architecture MVC</span>
            <span className="text-emerald-700 font-medium">Système synchronisé</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
