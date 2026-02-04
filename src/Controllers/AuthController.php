<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Utils\Helper;

class AuthController {
    public function login() {
        if (Auth::check()) {
            Helper::redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            session_destroy();
            Helper::redirect('/');
        }
    }
}
