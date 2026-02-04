<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Models;

use App\Core\Database;

class Subject {
    public static function all() {
        $pdo = Database::connect();
        return $pdo->query("
            SELECT id, name, slug, created_at
            FROM subjects
            ORDER BY name ASC
        ")->fetchAll();
    }

    public static function findByName($name) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM subjects WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public static function create($name, $slug) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO subjects (name, slug)
            VALUES (?, ?)
        ");
        $stmt->execute([$name, $slug]);
        return $pdo->lastInsertId();
    }

    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function countHomework($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM homework WHERE subject_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetch()['c'];
    }
}
