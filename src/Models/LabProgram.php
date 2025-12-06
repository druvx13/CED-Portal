<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class LabProgram {
    public static function all(int $limit = 20, int $offset = 0) {
        $sql = "
            SELECT
                lp.id,
                lp.title,
                lp.code,
                lp.created_at,
                lp.uploaded_by,
                u.username,
                COALESCE(pl.name, lp.language) AS language_name,
                COALESCE(pl.slug, lp.language) AS language_slug
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            LEFT JOIN users u ON lp.uploaded_by = u.id
            ORDER BY language_name ASC, lp.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        return (int)Database::getConnection()->query("SELECT COUNT(*) AS c FROM lab_programs")->fetch()['c'];
    }

    public static function find(int $id) {
        $stmt = Database::getConnection()->prepare("
            SELECT
                lp.*,
                u.username,
                COALESCE(pl.name, lp.language) AS language_name,
                COALESCE(pl.slug, lp.language) AS language_slug
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            LEFT JOIN users u ON lp.uploaded_by = u.id
            WHERE lp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create($title, $code, $languageString, $languageId, $outputPath, $uploadedBy) {
        $stmt = Database::getConnection()->prepare("
            INSERT INTO lab_programs (title, code, language, language_id, output_path, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $code, $languageString, $languageId, $outputPath, $uploadedBy]);
        return Database::getConnection()->lastInsertId();
    }

    public static function update($id, $title, $code, $languageString, $languageId, $outputPath) {
        $stmt = Database::getConnection()->prepare("
            UPDATE lab_programs
            SET title = ?, code = ?, language = ?, language_id = ?, output_path = ?
            WHERE id = ?
        ");
        return $stmt->execute([$title, $code, $languageString, $languageId, $outputPath, $id]);
    }

    public static function delete($id) {
        $stmt = Database::getConnection()->prepare("DELETE FROM lab_programs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getRecent(int $limit = 5) {
        $stmt = Database::getConnection()->prepare("
            SELECT
                lp.id,
                lp.title,
                lp.code,
                lp.created_at,
                lp.uploaded_by,
                u.username,
                COALESCE(pl.name, lp.language) AS language_name,
                COALESCE(pl.slug, lp.language) AS language_slug
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            LEFT JOIN users u ON lp.uploaded_by = u.id
            ORDER BY lp.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findByUserId(int $userId, int $limit, int $offset) {
        $stmt = Database::getConnection()->prepare("
            SELECT lp.id, lp.title, lp.created_at,
                   COALESCE(pl.name, lp.language) AS language_name
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            WHERE lp.uploaded_by = ?
            ORDER BY lp.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByUserId(int $userId) {
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) AS c FROM lab_programs WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch()['c'];
    }
}
