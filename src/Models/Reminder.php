<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Reminder {
    public static function getUpcomingForUser(int $userId, int $limit = 3) {
        $stmt = Database::getConnection()->prepare("
            SELECT id, message, due_date
            FROM reminders
            WHERE user_id = ? AND due_date >= NOW()
            ORDER BY due_date ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllForUser(int $userId) {
        $stmt = Database::getConnection()->prepare("
            SELECT * FROM reminders
            WHERE user_id = ?
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $message, string $dueDate) {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO reminders (user_id, message, due_date)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $message, $dueDate]);
        return Database::getConnection()->lastInsertId();
    }
}
