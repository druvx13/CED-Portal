<?php

namespace App\Core;

use App\Models\User;
use PDO;
use RuntimeException;

class Bootstrap {
    public static function run(): void {
        self::ensureAdminExists();
        self::ensureDirectories();
    }

    private static function ensureAdminExists(): void {
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->query("SELECT id FROM users WHERE id = 1 LIMIT 1");
            $row  = $stmt->fetch();

            if (!$row) {
                $defaultUsername = 'admin';
                $defaultPassword = 'ChangeMe123!';

                $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (id, username, password_hash, is_admin, is_first_admin)
                    VALUES (1, ?, ?, 1, 1)
                ");
                $stmt->execute([$defaultUsername, $hash]);
            }
        } catch (\Exception $e) {
            // Log error or ignore if DB not ready
        }
    }

    private static function ensureDirectories(): void {
        $base = __DIR__ . '/../../public/uploads';
        $dirs = [
            $base,
            $base . '/code_outputs',
            $base . '/manuals',
            $base . '/homework_answers',
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }
}
