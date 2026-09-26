<div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
    <div>
        <h1 class="h4 fw-bold text-dark mb-1">Centre de Notifications</h1>
        <p class="text-muted small mb-0">Alertes temps réel, assignations d'activités et mises à jour des projets.</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($unreadCount > 0): ?>
            <form action="/notifications/read-all" method="POST" class="d-inline">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    Tout marquer comme lu (<?= (int)$unreadCount ?>)
                </button>
            </form>
        <?php endif; ?>
        <div class="btn-group btn-group-sm">
            <a href="/notifications" class="btn <?= !$unreadOnly ? 'btn-dark' : 'btn-outline-secondary' ?>">Toutes</a>
            <a href="/notifications?filter=unread" class="btn <?= $unreadOnly ? 'btn-dark' : 'btn-outline-secondary' ?>">
                Non lues <?= $unreadCount > 0 ? "({$unreadCount})" : '' ?>
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <?php if (empty($notifications)): ?>
        <div class="p-5 text-center text-muted">
            <div class="mb-2 fs-5">Aucune notification</div>
            <p class="small text-secondary mb-0">Vous êtes parfaitement à jour dans le suivi de vos projets.</p>
        </div>
    <?php else: ?>
        <div class="list-group list-group-flush rounded-3">
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item p-3 d-flex justify-content-between align-items-start <?= !$n['is_read'] ? 'bg-light bg-opacity-50' : '' ?>">
                    <div class="d-flex gap-3">
                        <div class="mt-1">
                            <?php if (!$n['is_read']): ?>
                                <span class="badge bg-primary rounded-pill p-1"> </span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill p-1 opacity-25"> </span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-light text-dark border font-monospace text-uppercase" style="font-size: 10px;">
                                    <?= e($n['type']) ?>
                                </span>
                                <h6 class="mb-0 fw-semibold text-dark fs-6"><?= e($n['title']) ?></h6>
                            </div>
                            <p class="text-secondary small mb-1"><?= e($n['message']) ?></p>
                            <span class="text-muted" style="font-size: 11px;"><?= e($n['created_at']) ?></span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if (!empty($n['link_url'])): ?>
                            <a href="<?= e($n['link_url']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;">Consulter</a>
                        <?php endif; ?>
                        <?php if (!$n['is_read']): ?>
                            <form action="/notifications/<?= (int)$n['id'] ?>/read" method="POST" class="d-inline">
                                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11px;">Marquer lue</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
