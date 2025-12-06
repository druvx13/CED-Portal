<?php
namespace Controllers;

use Core\Controller;
use Config\Database;
use Throwable;

class AdminController extends Controller {

    private function requireAdmin() {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
            http_response_code(403);
            die('Forbidden');
        }
    }

    private function safeSlug(string $name): string {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = bin2hex(random_bytes(4));
        }
        return $slug;
    }

    public function users() {
        $this->requireAdmin();
        $pdo = Database::connect();

        $users = $pdo->query("
            SELECT id, username, is_admin, is_first_admin, created_at
            FROM users
            ORDER BY id ASC
        ")->fetchAll();

        $this->view('layout/header', ['title' => 'Admin - Users']);
        $this->view('admin/users', ['users' => $users]);
        $this->view('layout/footer');
    }

    public function createUser() {
        $this->requireAdmin();
        // Only first admin can create users in original code
        if (empty($_SESSION['is_first_admin'])) {
             http_response_code(403);
             die("Only first admin can create users.");
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $is_admin = !empty($_POST['is_admin']) ? 1 : 0;
        $errors = [];
        $success = null;

        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }

        $pdo = Database::connect();

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username already taken.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password_hash, is_admin, is_first_admin, created_by)
                        VALUES (?, ?, ?, 0, ?)
                    ");
                    $stmt->execute([$username, $hash, $is_admin, $_SESSION['user_id']]);

                    $newId = (int)$pdo->lastInsertId();

                    $stmt = $pdo->prepare("
                        INSERT INTO user_audit (action, target_user_id, admin_id)
                        VALUES ('create', ?, ?)
                    ");
                    $stmt->execute([$newId, $_SESSION['user_id']]);

                    $pdo->commit();
                    $success = "User created (ID: {$newId}).";
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    $errors[] = "Error creating user.";
                }
            }
        }

        // Refetch users for the list
        $users = $pdo->query("
            SELECT id, username, is_admin, is_first_admin, created_at
            FROM users
            ORDER BY id ASC
        ")->fetchAll();

        $this->view('layout/header', ['title' => 'Admin - Users']);
        $this->view('admin/users', [
            'users' => $users,
            'errors' => $errors,
            'success' => $success
        ]);
        $this->view('layout/footer');
    }

    public function languages() {
        $this->requireAdmin();
        $pdo = Database::connect();

        $langs = $pdo->query("
            SELECT id, name, slug, created_at
            FROM programming_languages
            ORDER BY name ASC
        ")->fetchAll();

        $this->view('layout/header', ['title' => 'Admin - Languages']);
        $this->view('admin/languages', ['langs' => $langs]);
        $this->view('layout/footer');
    }

    public function manageLanguages() {
        $this->requireAdmin();
        $pdo = Database::connect();
        $errors = [];

        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $errors[] = "Language name is required.";
            } else {
                $slug = $this->safeSlug($name);
                $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
                $stmt->execute([$name]);
                if ($stmt->fetch()) {
                    $errors[] = "Language already exists.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO programming_languages (name, slug)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$name, $slug]);
                }
            }
        } elseif (isset($_POST['delete_id'])) {
            $id = (int)$_POST['delete_id'];
            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_programs WHERE language_id = ?");
            $stmt->execute([$id]);
            $c = (int)$stmt->fetch()['c'];
            if ($c > 0) {
                $errors[] = "Cannot delete language that is used by lab programs.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM programming_languages WHERE id = ?");
                $stmt->execute([$id]);
            }
        }

        $langs = $pdo->query("
            SELECT id, name, slug, created_at
            FROM programming_languages
            ORDER BY name ASC
        ")->fetchAll();

        $this->view('layout/header', ['title' => 'Admin - Languages']);
        $this->view('admin/languages', ['langs' => $langs, 'errors' => $errors]);
        $this->view('layout/footer');
    }

    public function subjects() {
        $this->requireAdmin();
        $pdo = Database::connect();

        $subjects = $pdo->query("
            SELECT id, name, slug, created_at
            FROM subjects
            ORDER BY name ASC
        ")->fetchAll();

        $this->view('layout/header', ['title' => 'Admin - Subjects']);
        $this->view('admin/subjects', ['subjects' => $subjects]);
        $this->view('layout/footer');
    }

    public function manageSubjects() {
        $this->requireAdmin();
        $pdo = Database::connect();
        $errors = [];

        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $errors[] = "Subject name is required.";
            } else {
                $slug = $this->safeSlug($name);
                $stmt = $pdo->prepare("SELECT id FROM subjects WHERE name = ?");
                $stmt->execute([$name]);
                if ($stmt->fetch()) {
                    $errors[] = "Subject already exists.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO subjects (name, slug)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$name, $slug]);
                }
            }
        } elseif (isset($_POST['delete_id'])) {
            $id = (int)$_POST['delete_id'];
            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM homework WHERE subject_id = ?");
            $stmt->execute([$id]);
            $c = (int)$stmt->fetch()['c'];
            if ($c > 0) {
                $errors[] = "Cannot delete subject that is used by homework.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
                $stmt->execute([$id]);
            }
        }

        $subjects = $pdo->query("
            SELECT id, name, slug, created_at
            FROM subjects
            ORDER BY name ASC
        ")->fetchAll();

        $this->view('layout/header', ['title' => 'Admin - Subjects']);
        $this->view('admin/subjects', ['subjects' => $subjects, 'errors' => $errors]);
        $this->view('layout/footer');
    }
}
