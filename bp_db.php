<?php
// db.php
declare(strict_types=1);

define('BASE_URL', 'https://yourdomain.example'); // CHANGE THIS

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = 'localhost';  // CHANGE
    $db   = 'your_database'; // CHANGE
    $user = 'your_user'; // CHANGE
    $pass = 'your_password'; // CHANGE
    $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * First-run initialization:
 * if user with id=1 does not exist, create default admin.
 */
function bootstrap_initial_admin(PDO $pdo): void {
    $stmt = $pdo->query("SELECT id FROM users WHERE id = 1 LIMIT 1");
    $row  = $stmt->fetch();

    if ($row) {
        return;
    }

    $defaultUsername = 'admin';
    $defaultPassword = 'ChangeMe123!'; // Tell user to change manually.

    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (id, username, password_hash, is_admin, is_first_admin)
        VALUES (1, ?, ?, 1, 1)
    ");
    $stmt->execute([$defaultUsername, $hash]);
}
