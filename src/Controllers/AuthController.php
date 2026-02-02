<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Core\CSRF;
use App\Core\Security;
use App\Utils\Helper;

class AuthController {
    public function login() {
        if (Auth::check()) {
            Helper::redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!CSRF::validateToken()) {
                View::render('auth/login', ['error' => 'Security token expired. Please try again.']);
                return;
            }

            // Rate limiting for login attempts
            if (!Security::checkRateLimit('login', 5, 300)) {
                View::render('auth/login', ['error' => 'Too many login attempts. Please try again later.']);
                return;
            }

            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $error    = null;

            // Direct query since User model returns filtered fields
            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = "Invalid credentials.";
            } else {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                Helper::redirect('/');
            }

            View::render('auth/login', ['error' => $error]);
            return;
        }

        View::render('auth/login');
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            CSRF::requireToken();
            
            session_destroy();
            Helper::redirect('/');
        }
    }
}
