<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'TaskFlow') ?> - TaskFlow Enterprise</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Style Personnalisé SaaS Professionnel -->
    <link rel="stylesheet" href="/taskflow/public/css/app.css">
</head>
<body class="d-flex flex-column h-100">

<header class="navbar-saas sticky-top">
    <div class="container-fluid px-lg-4 py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="/taskflow/public/dashboard" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                <span class="navbar-brand-mark">TF</span>
                <span class="fw-bold tracking-tight fs-6 text-slate-900">TaskFlow</span>
                <span class="text-muted small">/ Entreprise</span>
            </a>

            <?php if ($currentUser): ?>
                <nav class="d-none d-md-flex align-items-center gap-1 ms-4">
                    <a href="/taskflow/public/dashboard" class="nav-link-saas <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard') ? 'active' : '' ?>">
                        Tableau de bord
                    </a>
                    <?php if ((int)$currentUser['role_id'] === 1): ?>
                        <a href="/taskflow/public/admin/users" class="nav-link-saas <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'admin/users') ? 'active' : '' ?>">
                            Équipe &amp; Permissions
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-3">
            <?php if ($currentUser): ?>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 11px;">
                            <?= strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)) ?>
                        </div>
                        <span class="small fw-semibold text-dark"><?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                        <span class="text-muted small">(<?= e($currentUser['role_label'] ?? 'Membre') ?>)</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border text-small">
                        <li><span class="dropdown-item-text text-muted small"><?= e($currentUser['email']) ?></span></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="/taskflow/public/logout" method="POST" class="m-0">
                                <?= $csrfField ?>
                                <button type="submit" class="dropdown-item text-danger small">
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="d-flex gap-2">
                    <a href="/taskflow/public/login" class="btn btn-outline-secondary btn-sm">Connexion</a>
                    <a href="/taskflow/public/register" class="btn btn-dark btn-sm">Créer un compte</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="flex-shrink-0 py-4">
    <div class="container-fluid px-lg-4">
