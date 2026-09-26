# TaskFlow - Gestion de Projets & Tâches (PHP 8.2+ / MySQL)

TaskFlow est une application web modulaire de gestion de projets et suivi des tâches conçue avec une architecture MVC légère, performante et sécurisée (PHP 8.2+, PDO MySQL, Bootstrap 5, Vanilla JS).

---

## 🚀 Guide d'installation rapide (XAMPP / WAMP / MAMP)

### 1. Prérequis
- **PHP** : version **8.2** ou supérieure (extensions activées : `pdo_mysql`, `mbstring`, `session`, `json`).
- **MySQL / MariaDB** : version **5.7+** ou **MariaDB 10.3+**.
- **Serveur Web** : Apache avec le module `mod_rewrite` activé.

---

### 2. Déploiement des fichiers
Placez le dossier du projet dans le répertoire racine de votre serveur web :
- **XAMPP (Windows)** : `C:\xampp\htdocs\taskflow\`
- **WAMP (Windows)** : `C:\wamp64\www\taskflow\`
- **MAMP (macOS)** : `/Applications/MAMP/htdocs/taskflow/`
- **Linux Apache** : `/var/www/html/taskflow/`

---

### 3. Création et initialisation de la base de données
1. Ouvrez votre gestionnaire de base de données (ex: **phpMyAdmin** sur `http://localhost/phpmyadmin`).
2. Créez la base ou importez directement les scripts SQL situés dans le dossier `/database` :
   - Exécutez d'abord : `database/schema.sql` (crée la base `taskflow_db` et l'ensemble des tables).
   - Exécutez ensuite : `database/seeds.sql` (insère les rôles et les comptes de test).

Alternativement en ligne de commande MySQL :
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p taskflow_db < database/seeds.sql
```

---

### 4. Configuration de l'environnement (.env)
Copiez le fichier `.env.example` à la racine et renommez-le en `.env` :
```bash
cp .env.example .env
```
Ajustez les paramètres de connexion si votre base utilise un mot de passe :
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=taskflow_db
DB_USER=root
DB_PASS=
```

---

### 5. Accès à l'application
Ouvrez votre navigateur web et accédez à :
```text
http://localhost/taskflow/public/
```
Le fichier `public/.htaccess` prend en charge la réécriture automatique vers `index.php`.

---

## 🔑 Identifiants des comptes de test (Lot 1)

Tous les mots de passe respectent les exigences de sécurité et sont hachés en base avec `bcrypt` (coût 12) :

| Profil | Email | Mot de passe | Rôle | Droits principaux |
| :--- | :--- | :--- | :--- | :--- |
| **Administrateur** | `admin@taskflow.local` | `Admin@123456` | Admin (1) | Gestion des utilisateurs, rôles, audit log |
| **Chef de projet** | `manager@taskflow.local` | `Manager@123456` | Manager (2) | Gestion projets & affectation tâches |
| **Membre** | `member@taskflow.local` | `Member@123456` | Member (3) | Suivi de ses tâches & commentaires |

---

## 🛡️ Sécurité implémentée
- **Sessions durcies** : Cookies HTTPOnly, SameSite=Lax, régénération de session à la connexion.
- **Protection CSRF** : Jeton cryptographique validé sur toutes les requêtes POST.
- **Protection Injections SQL** : PDO avec `PDO::ATTR_EMULATE_PREPARES => false`.
- **Protection XSS** : Échappement systématique via le helper `e()`.
- **RBAC Serveur** : Vérification des rôles côté serveur dans le routeur et les contrôleurs.
- **Journal d'audit** : Traçabilité des connexions et changements de rôles en base de données.
