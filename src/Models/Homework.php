<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Homework {
    public static function all($limit, $offset) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT
                h.*,
                u.username,
                COALESCE(s.name, 'Uncategorized') AS subject_name
            FROM homework h
            LEFT JOIN subjects s ON h.subject_id = s.id
            LEFT JOIN users u ON h.uploaded_by = u.id
            ORDER BY subject_name ASC, h.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        $pdo = Database::connect();
        return (int)$pdo->query("SELECT COUNT(*) AS c FROM homework")->fetch()['c'];
    }

    public static function find($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM homework WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO homework (title, question, subject_id, due_date, answer_path, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['question'],
            $data['subject_id'],
            $data['due_date'],
            $data['answer_path'],
            $data['uploaded_by']
        ]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $data) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            UPDATE homework
            SET title = ?, question = ?, subject_id = ?, due_date = ?, answer_path = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['title'],
            $data['question'],
            $data['subject_id'],
            $data['due_date'],
            $data['answer_path'],
            $id
        ]);
    }

    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM homework WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function getByUser($userId, $limit, $offset) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT h.id, h.title, h.due_date, h.created_at,
                   COALESCE(s.name, 'Uncategorized') AS subject_name
            FROM homework h
            LEFT JOIN subjects s ON h.subject_id = s.id
            WHERE h.uploaded_by = :user_id
            ORDER BY h.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByUser($userId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM homework WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch()['c'];
    }
}
