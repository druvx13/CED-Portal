<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Core;

use App\Models\User;

class Auth {
    public static function user() {
        if (isset($_SESSION['user_id'])) {
            return User::find($_SESSION['user_id']);
        }
        return null;
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            http_response_code(401);
            header('Location: ' . \App\Config\Config::get('BASE_URL') . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void {
        $user = self::user();
        if (!$user || !$user['is_admin']) {
            http_response_code(403);
            echo "403 Forbidden";
            exit;
        }
    }

    public static function requireSuperAdmin(): void {
        $user = self::user();
        if (!$user || (int)$user['id'] !== 1 || !(int)$user['is_first_admin']) {
             http_response_code(403);
             echo "403 Forbidden: Only initial admin can perform this action.";
             exit;
        }
    }
}
