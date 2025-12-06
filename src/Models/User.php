<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function find($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, username, is_admin, is_first_admin FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByUsername($username) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function create($username, $passwordHash, $isAdmin, $createdBy) {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password_hash, is_admin, is_first_admin, created_by)
                VALUES (?, ?, ?, 0, ?)
            ");
            $stmt->execute([$username, $passwordHash, $isAdmin, $createdBy]);
            $newId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO user_audit (action, target_user_id, admin_id)
                VALUES ('create', ?, ?)
            ");
            $stmt->execute([$newId, $createdBy]);
            $pdo->commit();
            return $newId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function all($limit, $offset) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT id, username, is_admin, created_at
            FROM users
            ORDER BY username ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        $pdo = Database::connect();
        return (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    }
}
