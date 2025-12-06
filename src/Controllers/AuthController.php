<?php
namespace Controllers;

use Core\Controller;
use Config\Database;

class AuthController extends Controller {
    public function loginForm() {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $this->view('layout/header', ['title' => 'Login']);
        $this->view('auth/login');
        $this->view('layout/footer');
    }

    public function login() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = null;

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid credentials.";
            $this->view('layout/header', ['title' => 'Login']);
            $this->view('auth/login', ['error' => $error]);
            $this->view('layout/footer');
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['is_first_admin'] = $user['is_first_admin'];
            $this->redirect('/');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
