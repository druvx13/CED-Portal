<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Note {
    public static function getAllForUser(int $userId) {
        $stmt = Database::getConnection()->prepare("
            SELECT * FROM notes
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $title, string $body) {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO notes (user_id, title, body)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $body]);
        return Database::getConnection()->lastInsertId();
    }
}
