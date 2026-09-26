<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card-saas p-4 p-md-5">
            <h1 class="h5 fw-semibold text-slate-900 border-bottom pb-3 mb-4">Créer un nouveau projet</h1>

            <form action="/taskflow/public/projects" method="POST">
                <?= $csrfField ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label small fw-medium">Nom du projet *</label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name" required placeholder="Ex : Refonte Portail Client">
                    </div>
                    <div class="col-md-4">
                        <label for="code_prefix" class="form-label small fw-medium">Code Préfixe (3-6 car.) *</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" id="code_prefix" name="code_prefix" maxlength="8" required placeholder="PRJ-A">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-medium">Description et objectifs</label>
                    <textarea class="form-control form-control-sm" id="description" name="description" rows="3" placeholder="Périmètre du projet, livrables attendus..."></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label small fw-medium">Date de démarrage</label>
                        <input type="date" class="form-control form-control-sm" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-6">
                        <label for="due_date" class="form-label small fw-medium">Date d'échéance cible</label>
                        <input type="date" class="form-control form-control-sm" id="due_date" name="due_date">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-medium">Membres assignés au projet</label>
                    <div class="card p-3 bg-light border-0" style="max-height: 160px; overflow-y: auto;">
                        <?php foreach ($users as $u): ?>
                            <div class="form-check small mb-1">
                                <input class="form-check-input" type="checkbox" name="member_ids[]" value="<?= (int)$u['id'] ?>" id="user_<?= (int)$u['id'] ?>">
                                <label class="form-check-label text-slate-800" for="user_<?= (int)$u['id'] ?>">
                                    <?= e($u['first_name'] . ' ' . $u['last_name']) ?> (<?= e($u['role_label']) ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="/taskflow/public/projects" class="btn btn-outline-secondary btn-sm">Annuler</a>
                    <button type="submit" class="btn btn-dark btn-sm">Enregistrer le projet</button>
                </div>
            </form>
        </div>
    </div>
</div>
