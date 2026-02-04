<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Models;

use App\Core\Database;

class Note {
    public static function allForUser($userId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT * FROM notes
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create($userId, $title, $body) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO notes (user_id, title, body)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $body]);
        return $pdo->lastInsertId();
    }
}
