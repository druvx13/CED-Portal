<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

class AuthController {
    public function login() {
        if (Auth::user()) {
            header('Location: ' . getenv('BASE_URL'));
            exit;
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $user = User::findByUsername($username);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = "Invalid credentials.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                header('Location: ' . getenv('BASE_URL'));
                exit;
            }
        }

        $view = new View();
        $view->render('auth/login', ['error' => $error], 'Login');
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_destroy();
            header('Location: ' . getenv('BASE_URL'));
            exit;
        }
    }
}
