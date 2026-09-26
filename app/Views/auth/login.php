<div class="row justify-content-center py-5">
    <div class="col-12 col-md-5 col-lg-4">
        <div class="card-saas p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="navbar-brand-mark mx-auto mb-3" style="width: 40px; height: 40px; font-size: 15px;">TF</div>
                <h1 class="h5 fw-semibold text-slate-900 mb-1">Connexion à votre espace</h1>
                <p class="text-muted small">TaskFlow Enterprise &bull; Authentification sécurisée</p>
            </div>

            <form action="/taskflow/public/login" method="POST" autocomplete="off">
                <?= $csrfField ?>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-medium text-slate-700">Adresse email</label>
                    <input type="email" class="form-control form-control-sm" id="email" name="email" placeholder="nom@entreprise.com" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-medium text-slate-700">Mot de passe</label>
                    <input type="password" class="form-control form-control-sm" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-dark btn-sm py-2 fw-medium">
                        Se connecter
                    </button>
                </div>

                <div class="text-center text-muted small">
                    Nouveau collaborateur ? <a href="/taskflow/public/register" class="text-decoration-none text-dark fw-semibold">Créer un compte</a>
                </div>
            </form>

            <hr class="my-4">

            <div class="bg-light p-3 rounded small text-slate-600">
                <div class="fw-semibold text-slate-900 mb-1">Comptes de test (Lot 1) :</div>
                <ul class="list-unstyled mb-0 font-monospace" style="font-size: 11px;">
                    <li><strong>Admin :</strong> admin@taskflow.local (Admin@123456)</li>
                    <li><strong>Manager :</strong> manager@taskflow.local (Manager@123456)</li>
                    <li><strong>Membre :</strong> member@taskflow.local (Member@123456)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
