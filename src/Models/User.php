<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function find(int $id) {
        $stmt = Database::getConnection()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUsername(string $username) {
        $stmt = Database::getConnection()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $username, string $password, bool $isAdmin, int $createdBy) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, is_admin, is_first_admin, created_by)
            VALUES (?, ?, ?, 0, ?)
        ");
        $stmt->execute([$username, $hash, $isAdmin ? 1 : 0, $createdBy]);
        return $pdo->lastInsertId();
    }

    public static function all(int $limit = 20, int $offset = 0) {
        $stmt = Database::getConnection()->prepare("
            SELECT id, username, is_admin, is_first_admin, created_at
            FROM users
            ORDER BY username ASC
            LIMIT ? OFFSET ?
        ");
        // PDO limit requires integer binding
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        return (int)Database::getConnection()->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    }
}
