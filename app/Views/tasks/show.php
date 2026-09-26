<?php
use App\Models\Comment;
$commentModel = new Comment();
$comments = $commentModel->getCommentsForTask((int)$task['id']);
?>

<div class="mb-4">
    <a href="/taskflow/public/projects/<?= (int)$task['project_id'] ?>" class="text-decoration-none small text-muted">
        &larr; Retour au projet [<?= e($task['code_prefix']) ?>] <?= e($task['project_name']) ?>
    </a>
</div>

<div class="row g-4">
    <!-- Colonne Principale : Titre, Description & Fil de Commentaires -->
    <div class="col-12 col-lg-8">
        <div class="card-saas p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-monospace small text-muted">[<?= e($task['code_prefix']) ?>-<?= (int)$task['id'] ?>]</span>
                <span class="badge-subtle badge-subtle-admin"><?= ucfirst(e($task['priority'])) ?></span>
            </div>

            <h1 class="h4 fw-semibold text-slate-900 mb-3"><?= e($task['title']) ?></h1>

            <div class="text-slate-700 small leading-relaxed border-bottom pb-4 mb-4">
                <?= nl2br(e($task['description'] ?: 'Aucune description détaillée fournie.')) ?>
            </div>

            <?php if ($task['status'] === 'bloquee' && !empty($task['blocked_reason'])): ?>
                <div class="p-3 rounded bg-danger-subtle text-danger border border-danger-subtle small mb-4">
                    <strong>Motif du blocage :</strong> <?= e($task['blocked_reason']) ?>
                </div>
            <?php endif; ?>

            <!-- Fil des commentaires -->
            <div class="pt-2">
                <h2 class="h6 fw-semibold text-slate-900 mb-3">Échanges &amp; Commentaires (<?= count($comments) ?>)</h2>

                <?php if (empty($comments)): ?>
                    <p class="text-muted small py-3">Aucun commentaire pour le moment. Démarrez la discussion ci-dessous.</p>
                <?php else: ?>
                    <div class="space-y-3 mb-4">
                        <?php foreach ($comments as $c): ?>
                            <div class="p-3 rounded bg-slate-50 border border-slate-200 mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold text-slate-900 small">
                                            <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                                        </span>
                                        <span class="text-muted small" style="font-size: 11px;">
                                            (<?= e($c['role_label']) ?>) &bull; <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?>
                                        </span>
                                    </div>

                                    <?php if ((int)$c['user_id'] === (int)$currentUser['id'] || (int)$currentUser['role_id'] === 1): ?>
                                        <form action="/taskflow/public/comments/<?= (int)$c['id'] ?>/delete" method="POST" class="m-0">
                                            <?= $csrfField ?>
                                            <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none small" style="font-size: 11px;" onclick="return confirm('Supprimer ce commentaire ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <div class="text-slate-800 small">
                                    <?= nl2br(e($c['content'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire d'ajout de commentaire -->
                <form action="/taskflow/public/tasks/<?= (int)$task['id'] ?>/comments" method="POST" class="mt-4">
                    <?= $csrfField ?>
                    <div class="mb-2">
                        <label for="comment_content" class="form-label small fw-medium text-slate-700">Laisser un commentaire</label>
                        <textarea class="form-control form-control-sm" id="comment_content" name="content" rows="3" required placeholder="Partagez une avancée, posez une question ou signalez un point d'attention..."></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-dark btn-sm">Publier le commentaire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Colonne Latérale : Métadonnées & Changement de statut -->
    <div class="col-12 col-lg-4">
        <div class="card-saas p-4 mb-4">
            <h2 class="h6 fw-semibold text-slate-900 border-bottom pb-2 mb-3">Métadonnées</h2>

            <div class="mb-3">
                <label class="form-label small text-muted mb-1">Statut actuel</label>
                <form action="/taskflow/public/tasks/<?= (int)$task['id'] ?>/status" method="POST">
                    <?= $csrfField ?>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="a_faire" <?= $task['status'] === 'a_faire' ? 'selected' : '' ?>>À faire</option>
                        <option value="en_cours" <?= $task['status'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="en_revue" <?= $task['status'] === 'en_revue' ? 'selected' : '' ?>>En revue</option>
                        <option value="terminee" <?= $task['status'] === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                        <option value="bloquee" <?= $task['status'] === 'bloquee' ? 'selected' : '' ?>>Bloquée</option>
                    </select>
                </form>
            </div>

            <dl class="row small mb-0 text-slate-700">
                <dt class="col-5 text-muted fw-normal">Priorité :</dt>
                <dd class="col-7 fw-semibold"><?= ucfirst(e($task['priority'])) ?></dd>

                <dt class="col-5 text-muted fw-normal">Échéance :</dt>
                <dd class="col-7 font-monospace <?= $task['is_overdue'] ? 'text-danger fw-bold' : '' ?>">
                    <?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'Non définie' ?>
                </dd>

                <dt class="col-5 text-muted fw-normal">Estimation :</dt>
                <dd class="col-7 tabular-nums"><?= (float)$task['estimated_hours'] ?> h</dd>

                <dt class="col-5 text-muted fw-normal">Créé par :</dt>
                <dd class="col-7"><?= e($task['creator_first_name'] . ' ' . $task['creator_last_name']) ?></dd>
            </dl>

            <div class="border-top pt-3 mt-3">
                <div class="small text-muted fw-medium mb-2">Collaborateurs assignés</div>
                <?php if (empty($task['assignees'])): ?>
                    <span class="text-muted small italic">Aucun assigné.</span>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($task['assignees'] as $assignee): ?>
                            <span class="badge-subtle badge-subtle-admin">
                                <?= e($assignee['first_name'] . ' ' . $assignee['last_name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
