<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Core\Session;
use App\Core\Response;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (AuthMiddleware::check()) {
            Response::redirect('/taskflow/public/dashboard');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Connexion'
        ]);
    }

    public function login(): void
    {
        $this->validateCsrf();

        $email = strtolower(trim((string)$this->request->post('email', '')));
        $password = (string)$this->request->post('password', '');

        if (empty($email) || empty($password)) {
            Session::setFlash('danger', 'Veuillez saisir votre adresse email et votre mot de passe.');
            Response::redirect('/taskflow/public/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::setFlash('danger', 'Identifiants incorrects ou compte inexistant.');
            Response::redirect('/taskflow/public/login');
        }

        if ((int)$user['is_active'] !== 1) {
            Session::setFlash('warning', 'Votre compte est suspendu ou inactif. Contactez l\'administrateur.');
            Response::redirect('/taskflow/public/login');
        }

        // Connexion réussie : nettoyage des données sensibles avant stockage en session
        unset($user['password_hash']);
        AuthMiddleware::login($user);

        // Journal d'audit
        $logModel = new ActivityLog();
        $logModel->log('USER_LOGIN', (int)$user['id'], null, null, [
            'email' => $user['email'],
            'ip'    => $this->request->getClientIp()
        ]);

        Session::setFlash('success', "Bienvenue, {$user['first_name']} ! Vous êtes connecté(e).");
        Response::redirect('/taskflow/public/dashboard');
    }

    public function showRegister(): void
    {
        if (AuthMiddleware::check()) {
            Response::redirect('/taskflow/public/dashboard');
        }

        $this->render('auth/register', [
            'pageTitle' => 'Créer un compte'
        ]);
    }

    public function register(): void
    {
        $this->validateCsrf();

        $firstName = trim((string)$this->request->post('first_name', ''));
        $lastName = trim((string)$this->request->post('last_name', ''));
        $email = strtolower(trim((string)$this->request->post('email', '')));
        $password = (string)$this->request->post('password', '');
        $passwordConfirm = (string)$this->request->post('password_confirm', '');

        // Validation des règles métier
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            Session::setFlash('danger', 'Tous les champs marqués d\'un astérisque sont obligatoires.');
            Response::redirect('/taskflow/public/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'L\'adresse email saisie est invalide.');
            Response::redirect('/taskflow/public/register');
        }

        if (strlen($password) < 8) {
            Session::setFlash('danger', 'Le mot de passe doit comporter au moins 8 caractères.');
            Response::redirect('/taskflow/public/register');
        }

        if ($password !== $passwordConfirm) {
            Session::setFlash('danger', 'Les mots de passe saisis ne concordent pas.');
            Response::redirect('/taskflow/public/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            Session::setFlash('danger', 'Cette adresse email est déjà associée à un compte.');
            Response::redirect('/taskflow/public/register');
        }

        $newUserId = $userModel->create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'password'   => $password,
            'role_id'    => 3, // Rôle "Membre" par défaut
            'is_active'  => 1
        ]);

        // Audit log
        $logModel = new ActivityLog();
        $logModel->log('USER_REGISTER', $newUserId, null, null, ['email' => $email]);

        // Auto-connexion
        $user = $userModel->findWithRole($newUserId);
        unset($user['password_hash']);
        AuthMiddleware::login($user);

        Session::setFlash('success', 'Votre compte a été créé avec succès ! Bienvenue sur TaskFlow.');
        Response::redirect('/taskflow/public/dashboard');
    }

    public function logout(): void
    {
        $this->validateCsrf();

        $user = AuthMiddleware::user();
        if ($user) {
            $logModel = new ActivityLog();
            $logModel->log('USER_LOGOUT', (int)$user['id']);
        }

        AuthMiddleware::logout();
        Session::start();
        Session::setFlash('info', 'Vous avez été déconnecté(e) avec succès.');
        Response::redirect('/taskflow/public/login');
    }
}
