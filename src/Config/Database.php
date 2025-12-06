<?php
namespace Config;

use PDO;

class Database {
    private static $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // Ideally these should come from environment variables
        $host = 'localhost';
        $db   = 'your_database';
        $user = 'your_user';
        $pass = 'your_password';
        $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
