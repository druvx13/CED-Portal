<?php
// public/index.php
declare(strict_types=1);

// Define Root Path
define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', 'https://yourdomain.example'); // CHANGE THIS

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = ROOT_PATH . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

session_start();

// Initialize Database & Bootstrap
$pdo = \Config\Database::connect();

// Bootstrap Initial Admin (Moved from global scope)
function bootstrap_initial_admin($pdo) {
    $stmt = $pdo->query("SELECT id FROM users WHERE id = 1 LIMIT 1");
    if (!$stmt->fetch()) {
        $defaultUsername = 'admin';
        $defaultPassword = 'ChangeMe123!';
        $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (id, username, password_hash, is_admin, is_first_admin) VALUES (1, ?, ?, 1, 1)");
        $stmt->execute([$defaultUsername, $hash]);
    }
}
bootstrap_initial_admin($pdo);

// Helper function h()
function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Router
$router = new \Core\Router();

// Define Routes
$router->add('GET', '/', 'DashboardController', 'index');
$router->add('GET', '/dashboard', 'DashboardController', 'index');
$router->add('GET', '/login', 'AuthController', 'loginForm');
$router->add('POST', '/login', 'AuthController', 'login');
$router->add('POST', '/logout', 'AuthController', 'logout');

// Lab Programs
$router->add('GET', '/lab-programs', 'LabProgramController', 'index');
$router->add('GET', '/lab-programs/new', 'LabProgramController', 'create');
$router->add('POST', '/lab-programs/new', 'LabProgramController', 'store');
$router->add('GET', '/lab-programs/view', 'LabProgramController', 'show');
$router->add('GET', '/lab-programs/edit', 'LabProgramController', 'edit');
$router->add('POST', '/lab-programs/edit', 'LabProgramController', 'update');
$router->add('POST', '/lab-programs/delete', 'LabProgramController', 'delete');

// Manuals
$router->add('GET', '/manuals', 'ManualController', 'index');
$router->add('GET', '/manuals/new', 'ManualController', 'create');
$router->add('POST', '/manuals/new', 'ManualController', 'store');
$router->add('GET', '/manuals/edit', 'ManualController', 'edit');
$router->add('POST', '/manuals/edit', 'ManualController', 'update');
$router->add('POST', '/manuals/delete', 'ManualController', 'delete');

// Homework
$router->add('GET', '/homework', 'HomeworkController', 'index');
$router->add('GET', '/homework/new', 'HomeworkController', 'create');
$router->add('POST', '/homework/new', 'HomeworkController', 'store');
$router->add('GET', '/homework/edit', 'HomeworkController', 'edit');
$router->add('POST', '/homework/edit', 'HomeworkController', 'update');
$router->add('POST', '/homework/delete', 'HomeworkController', 'delete');

// Admin
$router->add('GET', '/admin/users', 'AdminController', 'users');
$router->add('POST', '/admin/users', 'AdminController', 'createUser');
$router->add('GET', '/admin/languages', 'AdminController', 'languages');
$router->add('POST', '/admin/languages', 'AdminController', 'manageLanguages');
$router->add('GET', '/admin/subjects', 'AdminController', 'subjects');
$router->add('POST', '/admin/subjects', 'AdminController', 'manageSubjects');

// User Features
$router->add('GET', '/reminders', 'UserController', 'reminders');
$router->add('POST', '/reminders', 'UserController', 'storeReminder');
$router->add('GET', '/notes', 'UserController', 'notes');
$router->add('POST', '/notes', 'UserController', 'storeNote');
$router->add('GET', '/users', 'UserController', 'index');
$router->add('GET', '/users/posts', 'UserController', 'posts');

// Dispatch
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
