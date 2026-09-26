<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <div>
        <h1 class="h5 fw-semibold mb-1 text-slate-900">Projets</h1>
        <p class="text-muted small mb-0">Supervision des initiatives et suivi des portefeuilles d'activités</p>
    </div>
    <div>
        <?php if ((int)$user['role_id'] === 1 || (int)$user['role_id'] === 2): ?>
            <a href="/taskflow/public/projects/create" class="btn btn-dark btn-sm">
                Nouveau projet
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($projects)): ?>
    <div class="card-saas p-5 text-center bg-white">
        <h3 class="h6 fw-semibold text-slate-900 mb-1">Aucun projet actif</h3>
        <p class="text-muted small mb-3">Créez votre premier projet d'équipe pour structurer vos tâches.</p>
        <?php if ((int)$user['role_id'] === 1 || (int)$user['role_id'] === 2): ?>
            <a href="/taskflow/public/projects/create" class="btn btn-dark btn-sm">
                Créer un projet
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($projects as $prj): ?>
            <?php 
                $total = (int)($prj['total_tasks'] ?? 0);
                $done = (int)($prj['done_tasks'] ?? 0);
                $pct = $total > 0 ? (int)round(($done / $total) * 100) : 0;
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card-saas p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-monospace small fw-bold text-slate-900">
                                <?= e($prj['code_prefix']) ?>
                            </span>
                            <span class="badge-subtle badge-subtle-active">
                                <?= ucfirst(e($prj['status'])) ?>
                            </span>
                        </div>

                        <h2 class="h6 fw-semibold text-slate-900 mb-2">
                            <a href="/taskflow/public/projects/<?= (int)$prj['id'] ?>" class="text-decoration-none text-dark">
                                <?= e($prj['name']) ?>
                            </a>
                        </h2>

                        <p class="text-muted small mb-3 text-truncate-2" style="font-size: 12px; min-height: 36px;">
                            <?= e($prj['description'] ?: 'Aucune description renseignée.') ?>
                        </p>
                    </div>

                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between text-muted small mb-1" style="font-size: 11px;">
                            <span>Progression</span>
                            <span class="tabular-nums fw-medium text-slate-900"><?= $pct ?>% (<?= $done ?>/<?= $total ?>)</span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-dark" role="progressbar" style="width: <?= $pct ?>%;"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 text-muted small" style="font-size: 11px;">
                            <span>Chef : <strong><?= e($prj['owner_first_name'] . ' ' . $prj['owner_last_name']) ?></strong></span>
                            <span>Échéance : <?= e($prj['due_date'] ? date('d/m/Y', strtotime($prj['due_date'])) : 'Non définie') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
