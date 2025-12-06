<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Homework {
    public static function all(int $limit = 20, int $offset = 0) {
        $sql = "
            SELECT
                h.*,
                u.username,
                COALESCE(s.name, 'Uncategorized') AS subject_name
            FROM homework h
            LEFT JOIN subjects s ON h.subject_id = s.id
            LEFT JOIN users u ON h.uploaded_by = u.id
            ORDER BY subject_name ASC, h.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        return (int)Database::getConnection()->query("SELECT COUNT(*) AS c FROM homework")->fetch()['c'];
    }

    public static function find(int $id) {
        $stmt = Database::getConnection()->prepare("SELECT * FROM homework WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create($title, $question, $subjectId, $dueDate, $answerPath, $uploadedBy) {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO homework (title, question, subject_id, due_date, answer_path, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $question, $subjectId, $dueDate, $answerPath, $uploadedBy]);
        return Database::getConnection()->lastInsertId();
    }

    public static function update($id, $title, $question, $subjectId, $dueDate, $answerPath) {
        $stmt = Database::getConnection()->prepare("
            UPDATE homework
            SET title = ?, question = ?, subject_id = ?, due_date = ?, answer_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([$title, $question, $subjectId, $dueDate, $answerPath, $id]);
    }

    public static function delete($id) {
        $stmt = Database::getConnection()->prepare("DELETE FROM homework WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function findByUserId(int $userId, int $limit, int $offset) {
        $stmt = Database::getConnection()->prepare("
            SELECT h.id, h.title, h.due_date, h.created_at,
                   COALESCE(s.name, 'Uncategorized') AS subject_name
            FROM homework h
            LEFT JOIN subjects s ON h.subject_id = s.id
            WHERE h.uploaded_by = ?
            ORDER BY h.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByUserId(int $userId) {
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) AS c FROM homework WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch()['c'];
    }
}
