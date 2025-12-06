<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class LabManual {
    public static function getRecent($limit = 3) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT id, title, pdf_path, created_at
            FROM lab_manuals
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function all($limit, $offset) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT lm.*, u.username
            FROM lab_manuals lm
            LEFT JOIN users u ON lm.uploaded_by = u.id
            ORDER BY lm.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        $pdo = Database::connect();
        return (int)$pdo->query("SELECT COUNT(*) AS c FROM lab_manuals")->fetch()['c'];
    }

    public static function find($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($title, $pdfPath, $uploadedBy) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO lab_manuals (title, pdf_path, uploaded_by)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$title, $pdfPath, $uploadedBy]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $title, $pdfPath) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            UPDATE lab_manuals
            SET title = ?, pdf_path = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $pdfPath, $id]);
    }

    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function getByUser($userId, $limit, $offset) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT id, title, pdf_path, created_at
            FROM lab_manuals
            WHERE uploaded_by = :user_id
            ORDER BY created_at DESC
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
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_manuals WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch()['c'];
    }
}
