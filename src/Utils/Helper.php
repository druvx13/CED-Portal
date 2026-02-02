<?php

namespace App\Utils;

use App\Core\Security;
use App\Core\CSRF;

class Helper {
    /**
     * HTML escape (alias for Security::escape for backwards compatibility)
     */
    public static function h(?string $v): string {
        return Security::escape($v ?? '');
    }

    /**
     * Get CSRF token field for forms
     */
    public static function csrfField(): string {
        return CSRF::getTokenField();
    }

    public static function safeSlug(string $name): string {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = bin2hex(random_bytes(4));
        }
        return $slug;
    }

    public static function redirect(string $path): void {
        $baseUrl = \App\Config\Config::get('BASE_URL');
        if (strpos($path, 'http') !== 0) {
            $path = rtrim($baseUrl, '/') . $path;
        }
        header('Location: ' . $path);
        exit;
    }
}
