<?php

namespace App\Utils;

class Helper {
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
        $baseUrl = getenv('BASE_URL') ?: '';
        if (strpos($path, 'http') !== 0) {
            $path = rtrim($baseUrl, '/') . $path;
        }
        header('Location: ' . $path);
        exit;
    }
}
