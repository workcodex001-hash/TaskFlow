<div class="row justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 56px; height: 56px; font-size: 24px;">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h3 class="fw-bold">Inscription</h3>
                    <p class="text-muted small">Rejoignez l'équipe sur TaskFlow</p>
                </div>

                <form action="/taskflow/public/register" method="POST" autocomplete="off">
                    <?= $csrfField ?>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-semibold">Prénom *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Jean">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-semibold">Nom *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Dupont">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Adresse email *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="jean.dupont@entreprise.com">
                        </div>
                        <div class="form-text">Elle servira d'identifiant de connexion unique.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mot de passe *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required placeholder="Au moins 8 caractères">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-semibold">Confirmer le mot de passe *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-shield-check"></i></span>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required placeholder="Répéter le mot de passe">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> Créer mon compte
                        </button>
                    </div>

                    <div class="text-center text-muted small">
                        Déjà un compte ? <a href="/taskflow/public/login" class="text-decoration-none fw-semibold">Se connecter</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
