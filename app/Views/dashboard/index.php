<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <div>
        <h1 class="h5 fw-semibold mb-1 text-slate-900">Tableau de bord consolidé</h1>
        <p class="text-muted small mb-0">Indicateurs clés de performance &bull; Charge d'équipe &bull; Avancement des livrables</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/taskflow/public/tasks" class="btn btn-outline-secondary btn-sm">
            Recherche de tâches
        </a>
        <a href="/taskflow/public/projects" class="btn btn-dark btn-sm">
            Voir les projets
        </a>
    </div>
</div>

<!-- 1. Cartes des 5 Statuts & Taux d'avancement -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card-saas p-3">
            <div class="text-muted small fw-medium">À faire</div>
            <div class="fs-4 fw-bold text-dark mt-1 tabular-nums"><?= $statusCounts['a_faire'] ?></div>
            <div class="text-muted small" style="font-size: 11px;">En attente de démarrage</div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card-saas p-3">
            <div class="text-muted small fw-medium">En cours</div>
            <div class="fs-4 fw-bold text-primary mt-1 tabular-nums"><?= $statusCounts['en_cours'] ?></div>
            <div class="text-muted small" style="font-size: 11px;">En développement actif</div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card-saas p-3">
            <div class="text-muted small fw-medium">En revue</div>
            <div class="fs-4 fw-bold text-warning mt-1 tabular-nums"><?= $statusCounts['en_revue'] ?></div>
            <div class="text-muted small" style="font-size: 11px;">Validation &amp; tests</div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card-saas p-3">
            <div class="text-muted small fw-medium">Terminées</div>
            <div class="fs-4 fw-bold text-success mt-1 tabular-nums"><?= $statusCounts['terminee'] ?></div>
            <div class="text-muted small" style="font-size: 11px;">Livrées avec succès</div>
        </div>
    </div>

    <div class="col-12 col-lg">
        <div class="card-saas p-3 border-danger-subtle bg-danger-subtle bg-opacity-25">
            <div class="text-danger small fw-semibold">Bloquées</div>
            <div class="fs-4 fw-bold text-danger mt-1 tabular-nums"><?= $statusCounts['bloquee'] ?></div>
            <div class="text-danger small" style="font-size: 11px;">Motif obligatoire requis</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- 2. Tâches en retard critique -->
    <div class="col-12 col-lg-7">
        <div class="card-saas h-100 overflow-hidden">
            <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                <h2 class="h6 fw-semibold text-slate-900 mb-0">Tâches en retard critique</h2>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                    <?= count($overdueTasks) ?> en retard
                </span>
            </div>

            <?php if (empty($overdueTasks)): ?>
                <div class="p-5 text-center text-muted small">
                    Aucune tâche en retard sur l'ensemble des projets actifs.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table-saas">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Tâche</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th class="text-end">Échéance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdueTasks as $ot): ?>
                                <tr>
                                    <td class="font-monospace fw-bold small">[<?= e($ot['code_prefix']) ?>-<?= (int)$ot['id'] ?>]</td>
                                    <td>
                                        <a href="/taskflow/public/tasks/<?= (int)$ot['id'] ?>" class="text-decoration-none text-dark fw-medium">
                                            <?= e($ot['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-subtle badge-subtle-admin"><?= ucfirst(e($ot['priority'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-subtle <?= $ot['status'] === 'bloquee' ? 'badge-subtle-inactive' : 'badge-subtle-admin' ?>">
                                            <?= ucfirst(e(str_replace('_', ' ', $ot['status']))) ?>
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace text-danger fw-bold tabular-nums">
                                        <?= date('d/m/Y', strtotime($ot['due_date'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Charge de travail par collaborateur -->
    <div class="col-12 col-lg-5">
        <div class="card-saas h-100 p-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h2 class="h6 fw-semibold text-slate-900 mb-0">Charge active par collaborateur</h2>
                <span class="text-muted small">Tâches non closes</span>
            </div>

            <div class="space-y-3">
                <?php foreach ($workload as $w): ?>
                    <?php 
                        $activeCount = (int)$w['active_tasks'];
                        $barPct = min(100, $activeCount * 20); // Scale
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-xs mb-1">
                            <span class="fw-medium text-slate-900"><?= e($w['first_name'] . ' ' . $w['last_name']) ?></span>
                            <span class="tabular-nums fw-semibold <?= $activeCount > 3 ? 'text-danger' : 'text-slate-700' ?>">
                                <?= $activeCount ?> tâche<?= $activeCount > 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar <?= $activeCount > 3 ? 'bg-danger' : 'bg-dark' ?>" role="progressbar" style="width: <?= $barPct ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- 4. Avancement des Projets -->
<div class="card-saas p-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h2 class="h6 fw-semibold text-slate-900 mb-0">Avancement des Projets Actifs</h2>
        <span class="text-muted small">Taux de complétion moyen : <?= $completionRate ?>%</span>
    </div>

    <div class="row g-3">
        <?php foreach ($projects as $p): ?>
            <?php 
                $pTotal = (int)($p['total_tasks'] ?? 0);
                $pDone = (int)($p['done_tasks'] ?? 0);
                $pPct = $pTotal > 0 ? (int)round(($pDone / $pTotal) * 100) : 0;
            ?>
            <div class="col-12 col-md-6">
                <div class="p-3 rounded border border-slate-200 bg-slate-50">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="font-monospace small fw-bold text-dark">[<?= e($p['code_prefix']) ?>] <?= e($p['name']) ?></span>
                        <span class="tabular-nums small fw-semibold text-slate-900"><?= $pPct ?>%</span>
                    </div>
                    <div class="progress mb-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" role="progressbar" style="width: <?= $pPct ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small" style="font-size: 11px;">
                        <span>Chef : <?= e($p['owner_first_name'] . ' ' . $p['owner_last_name']) ?></span>
                        <span><?= $pDone ?> / <?= $pTotal ?> terminées</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
