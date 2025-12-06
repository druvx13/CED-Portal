<?php

namespace App\Core;

use App\Models\User;

class Auth {
    public static function user(): ?array {
        if (!empty($_SESSION['user_id'])) {
            // In a real app we might cache this request or store user data in session
            // For now, we fetch from DB to be fresh
            return User::find($_SESSION['user_id']);
        }
        return null;
    }

    public static function requireLogin(): void {
        if (!self::user()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Location: ' . getenv('BASE_URL') . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void {
        $user = self::user();
        if (!$user || !$user['is_admin']) {
            header('HTTP/1.1 403 Forbidden');
            echo "403 Forbidden";
            exit;
        }
    }
}
