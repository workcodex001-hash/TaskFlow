<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Gestionnaire de connexion MySQL via PDO (Pattern Singleton)
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Singleton
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_NAME'] ?? 'taskflow_db';
            $user = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Sécurité renforcée contre les injections SQL
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                // En production, masquer les détails sensibles
                die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
            }
        }

        return self::$instance;
    }
}
