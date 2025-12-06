<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class LabManual {
    public static function all(int $limit = 20, int $offset = 0) {
        $sql = "
            SELECT lm.*, u.username
            FROM lab_manuals lm
            LEFT JOIN users u ON lm.uploaded_by = u.id
            ORDER BY lm.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        return (int)Database::getConnection()->query("SELECT COUNT(*) AS c FROM lab_manuals")->fetch()['c'];
    }

    public static function find(int $id) {
        $stmt = Database::getConnection()->prepare("SELECT * FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create($title, $pdfPath, $uploadedBy) {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO lab_manuals (title, pdf_path, uploaded_by)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$title, $pdfPath, $uploadedBy]);
        return Database::getConnection()->lastInsertId();
    }

    public static function update($id, $title, $pdfPath) {
        $stmt = Database::getConnection()->prepare("
            UPDATE lab_manuals
            SET title = ?, pdf_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([$title, $pdfPath, $id]);
    }

    public static function delete($id) {
        $stmt = Database::getConnection()->prepare("DELETE FROM lab_manuals WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getRecent(int $limit = 3) {
        $stmt = Database::getConnection()->prepare("
            SELECT id, title, pdf_path, created_at
            FROM lab_manuals
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findByUserId(int $userId, int $limit, int $offset) {
        $stmt = Database::getConnection()->prepare("
            SELECT id, title, pdf_path, created_at
            FROM lab_manuals
            WHERE uploaded_by = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByUserId(int $userId) {
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) AS c FROM lab_manuals WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch()['c'];
    }
}
