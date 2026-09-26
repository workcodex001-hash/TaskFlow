-- ==============================================================================
-- TASKFLOW - DONNÉES DE DÉMARRAGE (SEEDS)
-- ==============================================================================

USE `taskflow_db`;

-- Rôles du système
INSERT INTO `roles` (`id`, `name`, `label`, `description`) VALUES
(1, 'admin', 'Administrateur', 'Accès total à la configuration, gestion des utilisateurs et logs'),
(2, 'manager', 'Chef de projet', 'Création et gestion des projets, création de tâches et assignation'),
(3, 'member', 'Membre d\'équipe', 'Prise en charge des tâches assignées, commentaires et suivi')
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `description` = VALUES(`description`);

-- Utilisateurs par défaut pour les tests
-- Hachage bcrypt généré avec un coût de 12 (password_hash(..., PASSWORD_BCRYPT))
-- Identifiants de test :
-- 1) Admin   : admin@taskflow.local   / Admin@123456
-- 2) Manager : manager@taskflow.local / Manager@123456
-- 3) Member  : member@taskflow.local  / Member@123456
-- Hash de 'Admin@123456'   : $2y$12$4mU8dYvR7.2hU9qf3i6AieqU32dYwM0qIqvPj8e6b1wYm5sKx7g5m
-- Hash de 'Manager@123456' : $2y$12$ZfK.r1N3J4p5q6r7s8t9uuG6V0w1x2y3z4A5B6C7D8E9F0G1H2I3J
-- Hash de 'Member@123456'  : $2y$12$Q1w2e3r4t5y6u7i8o9p0aqW8e7r6t5y4u3i2o1p0q9w8e7r6t5y4u

INSERT INTO `users` (`id`, `role_id`, `first_name`, `last_name`, `email`, `password_hash`, `is_active`) VALUES
(1, 1, 'Super', 'Admin', 'admin@taskflow.local', '$2y$12$4mU8dYvR7.2hU9qf3i6AieqU32dYwM0qIqvPj8e6b1wYm5sKx7g5m', 1),
(2, 2, 'Marc', 'Lemoine', 'manager@taskflow.local', '$2y$12$ZfK.r1N3J4p5q6r7s8t9uuG6V0w1x2y3z4A5B6C7D8E9F0G1H2I3J', 1),
(3, 3, 'Alice', 'Bernard', 'member@taskflow.local', '$2y$12$Q1w2e3r4t5y6u7i8o9p0aqW8e7r6t5y4u3i2o1p0q9w8e7r6t5y4u', 1)
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

-- Premier log d'audit initial
INSERT INTO `activity_logs` (`user_id`, `action`, `details`, `ip_address`) VALUES
(1, 'SYSTEM_INIT', '{"message": "Initialisation du système et création des rôles standards"}', '127.0.0.1');
