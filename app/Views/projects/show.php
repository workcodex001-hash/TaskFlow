<div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
    <div>
        <div class="d-flex items-center gap-2 mb-1">
            <span class="font-monospace small fw-bold text-muted"><?= e($project['code_prefix']) ?></span>
            <span class="text-muted small">/</span>
            <h1 class="h5 fw-semibold text-slate-900 mb-0"><?= e($project['name']) ?></h1>
        </div>
        <p class="text-muted small mb-0">
            Responsable : <strong><?= e($project['owner_first_name'] . ' ' . $project['owner_last_name']) ?></strong> &bull;
            Échéance : <?= e($project['due_date'] ? date('d/m/Y', strtotime($project['due_date'])) : 'Non définie') ?>
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="/taskflow/public/tasks/create?project_id=<?= (int)$project['id'] ?>" class="btn btn-dark btn-sm">
            Nouvelle tâche
        </a>
    </div>
</div>

<!-- Membres de l'équipe du projet -->
<div class="d-flex align-items-center gap-2 mb-4 bg-white p-2 rounded border border-slate-200 small">
    <span class="text-muted ms-2" style="font-size: 11px;">Équipe du projet :</span>
    <div class="d-flex align-items-center gap-1">
        <?php foreach ($members as $m): ?>
            <span class="badge-subtle badge-subtle-admin" title="<?= e($m['email']) ?>">
                <?= e($m['first_name'] . ' ' . $m['last_name']) ?> (<?= e($m['role_label']) ?>)
            </span>
        <?php endforeach; ?>
    </div>
</div>

<!-- Colonnes Kanban (5 Statuts) -->
<?php
$columns = [
    'a_faire'   => ['label' => 'À faire',   'color' => '#64748b'],
    'en_cours'  => ['label' => 'En cours',  'color' => '#2563eb'],
    'en_revue'  => ['label' => 'En revue',  'color' => '#d97706'],
    'terminee'  => ['label' => 'Terminée',  'color' => '#059669'],
    'bloquee'   => ['label' => 'Bloquée',   'color' => '#dc2626']
];
?>

<div class="row g-3">
    <?php foreach ($columns as $statusKey => $col): ?>
        <?php 
            $colTasks = array_filter($tasks, fn($t) => $t['status'] === $statusKey);
        ?>
        <div class="col-12 col-md-6 col-xl">
            <div class="card-saas p-2 bg-slate-50 border-slate-200 h-100">
                <div class="d-flex justify-content-between align-items-center px-2 py-1 mb-2">
                    <span class="small fw-bold text-slate-700" style="font-size: 12px;">
                        <?= $col['label'] ?>
                    </span>
                    <span class="badge bg-white text-dark border small tabular-nums" style="font-size: 10px;">
                        <?= count($colTasks) ?>
                    </span>
                </div>

                <div class="d-flex flex-column gap-2">
                    <?php if (empty($colTasks)): ?>
                        <div class="text-center py-4 text-muted small" style="font-size: 11px;">
                            Aucune tâche
                        </div>
                    <?php else: ?>
                        <?php foreach ($colTasks as $t): ?>
                            <div class="card-saas p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="font-monospace text-muted small" style="font-size: 10px;">
                                        #<?= (int)$t['id'] ?>
                                    </span>
                                    <span class="badge-subtle badge-subtle-admin" style="font-size: 10px;">
                                        <?= ucfirst(e($t['priority'])) ?>
                                    </span>
                                </div>

                                <div class="fw-semibold text-slate-900 small mb-2">
                                    <a href="/taskflow/public/tasks/<?= (int)$t['id'] ?>" class="text-decoration-none text-dark">
                                        <?= e($t['title']) ?>
                                    </a>
                                </div>

                                <?php if ($t['status'] === 'bloquee' && !empty($t['blocked_reason'])): ?>
                                    <div class="p-1.5 rounded bg-danger-subtle text-danger border border-danger-subtle small mb-2" style="font-size: 11px;">
                                        <strong>Blocage :</strong> <?= e($t['blocked_reason']) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Assignés & Échéance -->
                                <div class="d-flex justify-content-between align-items-center text-muted border-top pt-2 mt-1" style="font-size: 10px;">
                                    <div>
                                        <?php if (!empty($t['assignees'])): ?>
                                            <span>Assigné : <?= e(implode(', ', array_column($t['assignees'], 'name'))) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Non assigné</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($t['due_date'])): ?>
                                        <span class="<?= $t['is_overdue'] ? 'text-danger fw-bold' : '' ?>">
                                            <?= $t['is_overdue'] ? 'Retard : ' : '' ?><?= date('d/m', strtotime($t['due_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Déplacement rapide de statut -->
                                <div class="mt-2 pt-2 border-top">
                                    <form action="/taskflow/public/tasks/<?= (int)$t['id'] ?>/status" method="POST" class="m-0">
                                        <?= $csrfField ?>
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 10px; height: 26px;">
                                            <?php foreach ($columns as $sKey => $sVal): ?>
                                                <option value="<?= $sKey ?>" <?= $t['status'] === $sKey ? 'selected' : '' ?>>
                                                    Déplacer : <?= $sVal['label'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
