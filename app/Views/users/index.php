<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <div>
        <h1 class="h5 fw-semibold mb-1 text-slate-900">Équipe &amp; Permissions</h1>
        <p class="text-muted small mb-0">Contrôle d'accès basé sur les rôles (RBAC) &bull; Administration des privilèges</p>
    </div>
</div>

<div class="card-saas overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center bg-white">
        <h2 class="h6 fw-semibold text-slate-900 mb-0">Collaborateurs enregistrés</h2>
        <span class="text-muted small"><?= count($users) ?> comptes</span>
    </div>
    <div class="table-responsive">
        <table class="table-saas">
            <thead>
                <tr>
                    <th>Collaborateur</th>
                    <th>Adresse email</th>
                    <th>Rôle attribué (RBAC)</th>
                    <th>État du compte</th>
                    <th>Date d'inscription</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light border text-secondary d-flex align-items-center justify-content-center fw-semibold" style="width: 30px; height: 30px; font-size: 11px;">
                                    <?= strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold text-slate-900"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                    <div class="text-muted small" style="font-size: 11px;">ID #<?= (int)$u['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="font-monospace text-slate-600">
                            <?= e($u['email']) ?>
                        </td>
                        <td>
                            <form action="/taskflow/public/admin/users/role" method="POST" class="m-0 d-inline-block">
                                <?= $csrfField ?>
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <select name="role_id" class="form-select form-select-sm" onchange="this.form.submit()" style="font-size: 12px; width: 170px;">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>" <?= (int)$u['role_id'] === (int)$r['id'] ? 'selected' : '' ?>>
                                            <?= e($r['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?php if ((int)$u['is_active'] === 1): ?>
                                <span class="badge-subtle badge-subtle-active">Actif</span>
                            <?php else: ?>
                                <span class="badge-subtle badge-subtle-inactive">Suspendu</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small tabular-nums">
                            <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="text-end">
                            <form action="/taskflow/public/admin/users/toggle" method="POST" class="d-inline">
                                <?= $csrfField ?>
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="font-size: 11px;">
                                    <?= (int)$u['is_active'] === 1 ? 'Suspendre' : 'Activer' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
