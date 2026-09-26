<div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
    <div>
        <h1 class="h4 fw-bold text-dark mb-1">Journal d'Audit & Traçabilité</h1>
        <p class="text-muted small mb-0">Registre complet des actions système, authentifications et modifications de données (conforme ISO 27001).</p>
    </div>
    <?php if (\App\Core\AuthMiddleware::isAdmin()): ?>
        <div>
            <a href="/audit/export<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : '' ?>" 
               class="btn btn-dark btn-sm">
                Exporter en CSV
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Filtres de recherche d'audit -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/audit" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Collaborateur</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Tous les utilisateurs</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int)$u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= e($u['first_name'] . ' ' . $u['last_name']) ?> (<?= e($u['role_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small mb-1">Catégorie / Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">Toutes les actions</option>
                    <option value="AUTH" <?= ($filters['action'] ?? '') == 'AUTH' ? 'selected' : '' ?>>Authentification (Connexions / Déconnexions)</option>
                    <option value="ROLE" <?= ($filters['action'] ?? '') == 'ROLE' ? 'selected' : '' ?>>Contrôle d'accès & Rôles</option>
                    <option value="PROJECT" <?= ($filters['action'] ?? '') == 'PROJECT' ? 'selected' : '' ?>>Gestion de Projets</option>
                    <option value="TASK" <?= ($filters['action'] ?? '') == 'TASK' ? 'selected' : '' ?>>Gestion de Tâches</option>
                    <option value="COMMENT" <?= ($filters['action'] ?? '') == 'COMMENT' ? 'selected' : '' ?>>Commentaires & Discussions</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Date début</label>
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small mb-1">Date fin</label>
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Filtrer</button>
                <a href="/audit" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<!-- Table du journal d'audit -->
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 11px;">
                <tr>
                    <th scope="col" class="ps-3 py-3">Date & Heure</th>
                    <th scope="col" class="py-3">Utilisateur</th>
                    <th scope="col" class="py-3">Action</th>
                    <th scope="col" class="py-3">Cible / Contexte</th>
                    <th scope="col" class="py-3">Adresse IP</th>
                    <th scope="col" class="pe-3 py-3">Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Aucun événement d'audit ne correspond aux critères sélectionnés.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-3 text-nowrap font-monospace text-muted" style="font-size: 12px;">
                                <?= e($log['created_at']) ?>
                            </td>
                            <td>
                                <?php if (!empty($log['first_name'])): ?>
                                    <div class="fw-semibold text-dark"><?= e($log['first_name'] . ' ' . $log['last_name']) ?></div>
                                    <div class="text-muted" style="font-size: 11px;"><?= e($log['email'] ?? '') ?></div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Système / Anonyme</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace border" style="font-size: 11px;">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($log['task_title'])): ?>
                                    <span class="text-dark">Tâche : <?= e($log['task_title']) ?></span>
                                <?php elseif (!empty($log['project_name'])): ?>
                                    <span class="text-dark">Projet : <?= e($log['project_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="font-monospace text-muted" style="font-size: 11px;">
                                <?= e($log['ip_address']) ?>
                            </td>
                            <td class="pe-3 text-muted text-break" style="font-size: 11px; max-width: 250px;">
                                <?= e($log['details'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
