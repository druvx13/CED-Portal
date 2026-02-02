<?php

namespace App\Core;

/**
 * CSRF Protection System
 * Adapted from FluxBB's token-based CSRF protection
 */
class CSRF {
    private const TOKEN_NAME = 'csrf_token';
    private const SESSION_KEY = 'csrf_tokens';
    private const TOKEN_LIFETIME = 3600; // 1 hour

    /**
     * Generate a new CSRF token for the current session
     */
    public static function generateToken(): string {
        if (!isset($_SESSION)) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // Store token with timestamp
        $_SESSION[self::SESSION_KEY][$token] = time();

        // Clean old tokens
        self::cleanOldTokens();

        return $token;
    }

    /**
     * Get the token for inclusion in forms
     */
    public static function getTokenField(): string {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate CSRF token from request
     */
    public static function validateToken(?string $token = null): bool {
        if (!isset($_SESSION)) {
            session_start();
        }

        if ($token === null) {
            $token = $_POST[self::TOKEN_NAME] ?? $_GET[self::TOKEN_NAME] ?? null;
        }

        if ($token === null) {
            return false;
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // Check if token exists and is not expired
        if (isset($_SESSION[self::SESSION_KEY][$token])) {
            $timestamp = $_SESSION[self::SESSION_KEY][$token];
            
            if (time() - $timestamp <= self::TOKEN_LIFETIME) {
                // Token is valid, remove it (one-time use)
                unset($_SESSION[self::SESSION_KEY][$token]);
                return true;
            }
            
            // Token expired, remove it
            unset($_SESSION[self::SESSION_KEY][$token]);
        }

        return false;
    }

    /**
     * Require valid CSRF token or terminate request
     */
    public static function requireToken(): void {
        if (!self::validateToken()) {
            http_response_code(403);
            die('CSRF token validation failed. Please try again.');
        }
    }

    /**
     * Clean expired tokens from session
     */
    private static function cleanOldTokens(): void {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return;
        }

        $now = time();
        foreach ($_SESSION[self::SESSION_KEY] as $token => $timestamp) {
            if ($now - $timestamp > self::TOKEN_LIFETIME) {
                unset($_SESSION[self::SESSION_KEY][$token]);
            }
        }
    }

    /**
     * Get current token for AJAX requests
     */
    public static function getCurrentToken(): ?string {
        if (!isset($_SESSION[self::SESSION_KEY]) || empty($_SESSION[self::SESSION_KEY])) {
            return self::generateToken();
        }

        // Return the most recent token
        $tokens = $_SESSION[self::SESSION_KEY];
        arsort($tokens);
        reset($tokens);
        return key($tokens);
    }
}
