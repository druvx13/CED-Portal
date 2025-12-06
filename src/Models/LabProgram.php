<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class LabProgram {
    public static function getRecent($limit = 5) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
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
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function all($limit, $offset) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
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
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function count() {
        $pdo = Database::connect();
        return (int)$pdo->query("SELECT COUNT(*) AS c FROM lab_programs")->fetch()['c'];
    }

    public static function find($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
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
        return $stmt->fetch();
    }

    public static function create($data) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO lab_programs (title, code, language, language_id, output_path, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['code'],
            $data['language'],
            $data['language_id'],
            $data['output_path'],
            $data['uploaded_by']
        ]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $data) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            UPDATE lab_programs
            SET title = ?, code = ?, language = ?, language_id = ?, output_path = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['title'],
            $data['code'],
            $data['language'],
            $data['language_id'],
            $data['output_path'],
            $id
        ]);
    }

    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM lab_programs WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function getByUser($userId, $limit, $offset) {
         $pdo = Database::connect();
         $stmt = $pdo->prepare("
            SELECT lp.id, lp.title, lp.created_at,
                   COALESCE(pl.name, lp.language) AS language_name
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            WHERE lp.uploaded_by = :user_id
            ORDER BY lp.created_at DESC
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
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_programs WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetch()['c'];
    }
}
