<?php

require_once __DIR__ . '/../src/Autoloader.php';

use App\Config\Config;
use App\Core\Router;
use App\Core\Database;

// Load environment variables
Config::load(__DIR__ . '/../.env');

// Start session
session_start();

// Initialize DB (handles connection check)
try {
    $pdo = Database::connect();

    // Bootstrap initial admin if needed
    // This was in the original bp_db.php, moving logic here or to a helper
    $stmt = $pdo->query("SELECT id FROM users WHERE id = 1 LIMIT 1");
    if (!$stmt->fetch()) {
        $defaultUsername = 'admin';
        $defaultPassword = 'ChangeMe123!';
        $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (id, username, password_hash, is_admin, is_first_admin)
            VALUES (1, ?, ?, 1, 1)
        ");
        $stmt->execute([$defaultUsername, $hash]);
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$router = new Router();

// Auth
$router->get('/login', ['App\Controllers\AuthController', 'login']);
$router->post('/login', ['App\Controllers\AuthController', 'login']);
$router->post('/logout', ['App\Controllers\AuthController', 'logout']);

// Dashboard
$router->get('/', ['App\Controllers\DashboardController', 'index']);
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index']);

// Lab Programs
$router->get('/lab-programs', ['App\Controllers\LabProgramController', 'index']);
$router->get('/lab-programs/view', ['App\Controllers\LabProgramController', 'view']);
$router->get('/lab-programs/new', ['App\Controllers\LabProgramController', 'new']);
$router->post('/lab-programs/new', ['App\Controllers\LabProgramController', 'new']);
$router->get('/lab-programs/edit', ['App\Controllers\LabProgramController', 'edit']);
$router->post('/lab-programs/edit', ['App\Controllers\LabProgramController', 'edit']);
$router->post('/lab-programs/delete', ['App\Controllers\LabProgramController', 'delete']);

// Manuals
$router->get('/manuals', ['App\Controllers\ManualController', 'index']);
$router->get('/manuals/new', ['App\Controllers\ManualController', 'new']);
$router->post('/manuals/new', ['App\Controllers\ManualController', 'new']);
$router->get('/manuals/edit', ['App\Controllers\ManualController', 'edit']);
$router->post('/manuals/edit', ['App\Controllers\ManualController', 'edit']);
$router->post('/manuals/delete', ['App\Controllers\ManualController', 'delete']);

// Homework
$router->get('/homework', ['App\Controllers\HomeworkController', 'index']);
$router->get('/homework/view', ['App\Controllers\HomeworkController', 'view']);
$router->get('/homework/new', ['App\Controllers\HomeworkController', 'new']);
$router->post('/homework/new', ['App\Controllers\HomeworkController', 'new']);
$router->get('/homework/edit', ['App\Controllers\HomeworkController', 'edit']);
$router->post('/homework/edit', ['App\Controllers\HomeworkController', 'edit']);
$router->post('/homework/delete', ['App\Controllers\HomeworkController', 'delete']);

// Reminders
$router->get('/reminders', ['App\Controllers\ReminderController', 'index']);
$router->post('/reminders', ['App\Controllers\ReminderController', 'index']);

// Notes
$router->get('/notes', ['App\Controllers\NoteController', 'index']);
$router->post('/notes', ['App\Controllers\NoteController', 'index']);

// Users
$router->get('/users', ['App\Controllers\UserController', 'index']);
$router->get('/users/posts', ['App\Controllers\UserController', 'posts']);

// Admin
$router->get('/admin/users', ['App\Controllers\AdminController', 'users']);
$router->post('/admin/users', ['App\Controllers\AdminController', 'users']);
$router->get('/admin/languages', ['App\Controllers\AdminController', 'languages']);
$router->post('/admin/languages', ['App\Controllers\AdminController', 'languages']);
$router->get('/admin/subjects', ['App\Controllers\AdminController', 'subjects']);
$router->post('/admin/subjects', ['App\Controllers\AdminController', 'subjects']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
