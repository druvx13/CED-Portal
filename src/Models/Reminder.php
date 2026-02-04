<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Models;

use App\Core\Database;

class Reminder {
    public static function getUpcomingForUser($userId, $limit = 3) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT id, message, due_date
            FROM reminders
            WHERE user_id = ? AND due_date >= NOW()
            ORDER BY due_date ASC
            LIMIT $limit
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function allForUser($userId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT * FROM reminders
            WHERE user_id = ?
            ORDER BY due_date ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create($userId, $message, $dueDate) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO reminders (user_id, message, due_date)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $message, $dueDate]);
        return $pdo->lastInsertId();
    }
}
