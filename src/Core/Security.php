<?php

namespace App\Core;

/**
 * Security Middleware
 * Implements FluxBB-inspired security patterns
 */
class Security {
    /**
     * Apply security headers to response
     */
    public static function applySecurityHeaders(): void {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // XSS Protection
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "font-src 'self' https://cdnjs.cloudflare.com",
            "connect-src 'self'",
            "frame-ancestors 'self'"
        ]);
        header("Content-Security-Policy: $csp");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Remove server signature
        header_remove('X-Powered-By');
    }

    /**
     * Sanitize input data (adapted from FluxBB's forum_remove_bad_characters)
     */
    public static function sanitizeInput(array &$data): void {
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                // Remove null bytes and control characters
                $value = str_replace("\0", '', $value);
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
            }
        });
    }

    /**
     * HTML escape for safe output (like FluxBB's pun_htmlspecialchars)
     */
    public static function escape(?string $str): string {
        if ($str === null) {
            return '';
        }
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Validate integer ID
     */
    public static function validateId($id): int {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            http_response_code(400);
            die('Invalid ID parameter');
        }
        return $id;
    }

    /**
     * Sanitize filename for safe storage
     */
    public static function sanitizeFilename(string $filename): string {
        // Remove path traversal attempts
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Prevent hidden files
        $filename = ltrim($filename, '.');
        
        return $filename;
    }

    /**
     * Validate file upload
     */
    public static function validateFileUpload(array $file, array $allowedTypes, int $maxSize): array {
        $errors = [];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
            return $errors;
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed (' . number_format($maxSize / 1024 / 1024, 2) . ' MB)';
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $errors[] = 'File type not allowed';
        }

        // Additional check: validate file extension matches MIME type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $validExtensions = [
            'application/pdf' => ['pdf'],
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'text/plain' => ['txt'],
            'application/zip' => ['zip'],
        ];

        if (isset($validExtensions[$mimeType])) {
            if (!in_array($extension, $validExtensions[$mimeType], true)) {
                $errors[] = 'File extension does not match file type';
            }
        }

        return $errors;
    }

    /**
     * Rate limiting for sensitive actions
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 5, int $timeWindow = 300): bool {
        if (!isset($_SESSION)) {
            session_start();
        }

        $key = 'rate_limit_' . $action;
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }

        // Clean old attempts
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });

        // Check if limit exceeded
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return false;
        }

        // Record this attempt
        $_SESSION[$key][] = $now;
        return true;
    }

    /**
     * Generate secure random token
     */
    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validate and sanitize URL
     */
    public static function sanitizeUrl(string $url): ?string {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }
        return $url;
    }

    /**
     * Check if request is from same origin (CSRF protection helper)
     */
    public static function checkReferer(): bool {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }

        $referer = parse_url($_SERVER['HTTP_REFERER']);
        $server = parse_url($_SERVER['HTTP_HOST'] ?? '');

        return isset($referer['host'], $server['host']) && 
               $referer['host'] === $server['host'];
    }

    /**
     * Secure session configuration
     */
    public static function configureSession(): void {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
    }
}
