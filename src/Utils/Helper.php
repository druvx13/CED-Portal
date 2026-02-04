<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Utils;

class Helper {
    public static function h(string $v): string {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
