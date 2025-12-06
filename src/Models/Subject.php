<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Subject {
    public static function all() {
        $stmt = Database::getConnection()->query("
            SELECT id, name, slug, created_at
            FROM subjects
            ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function findByName(string $name) {
        $stmt = Database::getConnection()->prepare("SELECT * FROM subjects WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public static function create(string $name, string $slug) {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO subjects (name, slug)
            VALUES (?, ?)
        ");
        $stmt->execute([$name, $slug]);
        return Database::getConnection()->lastInsertId();
    }

    public static function delete(int $id) {
        $stmt = Database::getConnection()->prepare("DELETE FROM subjects WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function isUsed(int $id): bool {
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) AS c FROM homework WHERE subject_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetch()['c'] > 0;
    }
}
