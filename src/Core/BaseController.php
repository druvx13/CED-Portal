<?php

namespace App\Core;

use App\Core\CSRF;

/**
 * Base Controller with common functionality
 * Provides CSRF protection and other shared features
 */
abstract class BaseController {
    /**
     * Validate CSRF token for POST requests
     * 
     * @param bool $require If true, terminates on invalid token
     * @return bool True if token is valid or not required, false otherwise
     */
    protected function validateCSRF(bool $require = true): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $isValid = CSRF::validateToken();
        
        if (!$isValid && $require) {
            CSRF::requireToken(); // This will terminate execution
        }

        return $isValid;
    }

    /**
     * Require authentication (helper method)
     */
    protected function requireAuth(): void {
        Auth::requireLogin();
    }

    /**
     * Require admin privileges (helper method)
     */
    protected function requireAdmin(): void {
        Auth::requireAdmin();
    }

    /**
     * Get current authenticated user
     */
    protected function user(): ?array {
        return Auth::user();
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool {
        return Auth::check();
    }
}
