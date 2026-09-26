<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card-saas p-4 p-md-5">
            <h1 class="h5 fw-semibold text-slate-900 border-bottom pb-3 mb-4">Créer une nouvelle tâche</h1>

            <form action="/taskflow/public/tasks" method="POST">
                <?= $csrfField ?>

                <div class="mb-3">
                    <label for="project_id" class="form-label small fw-medium">Rattachement au projet *</label>
                    <select name="project_id" id="project_id" class="form-select form-select-sm" required>
                        <option value="">Sélectionner un projet...</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= $selectedProject === (int)$p['id'] ? 'selected' : '' ?>>
                                [<?= e($p['code_prefix']) ?>] <?= e($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label small fw-medium">Intitulé de la tâche *</label>
                    <input type="text" class="form-control form-control-sm" id="title" name="title" required placeholder="Ex : Mettre en place l'authentification avec Bcrypt">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-medium">Description détaillée</label>
                    <textarea class="form-control form-control-sm" id="description" name="description" rows="3" placeholder="Critères d'acceptation, spécifications techniques..."></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="priority" class="form-label small fw-medium">Priorité</label>
                        <select name="priority" id="priority" class="form-select form-select-sm">
                            <option value="basse">Basse</option>
                            <option value="moyenne" selected>Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label small fw-medium">Statut initial</label>
                        <select name="status" id="status" class="form-select form-select-sm" onchange="toggleBlockedReason(this.value)">
                            <option value="a_faire" selected>À faire</option>
                            <option value="en_cours">En cours</option>
                            <option value="en_revue">En revue</option>
                            <option value="terminee">Terminée</option>
                            <option value="bloquee">Bloquée</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="due_date" class="form-label small fw-medium">Date d'échéance</label>
                        <input type="date" class="form-control form-control-sm" id="due_date" name="due_date">
                    </div>
                </div>

                <div id="blocked_reason_group" class="mb-3 d-none">
                    <label for="blocked_reason" class="form-label small fw-medium text-danger">Motif obligatoire de blocage *</label>
                    <input type="text" class="form-control form-control-sm border-danger" id="blocked_reason" name="blocked_reason" placeholder="Ex : En attente de validation du schéma par le client">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-medium">Attribution multiple (Collaborateurs assignés)</label>
                    <div class="card p-3 bg-light border-0" style="max-height: 160px; overflow-y: auto;">
                        <?php foreach ($users as $u): ?>
                            <div class="form-check small mb-1">
                                <input class="form-check-input" type="checkbox" name="assignee_ids[]" value="<?= (int)$u['id'] ?>" id="assignee_<?= (int)$u['id'] ?>">
                                <label class="form-check-label text-slate-800" for="assignee_<?= (int)$u['id'] ?>">
                                    <?= e($u['first_name'] . ' ' . $u['last_name']) ?> (<?= e($u['role_label']) ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="/taskflow/public/tasks" class="btn btn-outline-secondary btn-sm">Annuler</a>
                    <button type="submit" class="btn btn-dark btn-sm">Créer la tâche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBlockedReason(status) {
    const group = document.getElementById('blocked_reason_group');
    if (status === 'bloquee') {
        group.classList.remove('d-none');
    } else {
        group.classList.add('d-none');
    }
}
</script>
