<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Core;

use PDO;
use App\Config\Config;

class Database {
    private static $pdo;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            $host = Config::get('DB_HOST', 'localhost');
            $db   = Config::get('DB_NAME', 'nhyfe_39084272_nikolus');
            $user = Config::get('DB_USER', 'root');
            $pass = Config::get('DB_PASS', '');

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }
}
