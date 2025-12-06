<?php
namespace Controllers;

use Core\Controller;
use Config\Database;

class UserController extends Controller {

    private function requireLogin() {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function reminders() {
        $this->requireLogin();
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT * FROM reminders
            WHERE user_id = ?
            ORDER BY due_date ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $rows = $stmt->fetchAll();

        $this->view('layout/header', ['title' => 'Reminders']);
        $this->view('users/reminders', ['reminders' => $rows]);
        $this->view('layout/footer');
    }

    public function storeReminder() {
        $this->requireLogin();
        $message  = trim($_POST['message'] ?? '');
        $due_date = trim($_POST['due_date'] ?? '');
        $errors = [];

        if ($message === '') {
            $errors[] = "Message is required.";
        }
        if ($due_date === '') {
            $errors[] = "Due date is required.";
        }

        $dt = null;
        if ($due_date !== '') {
            $dt = date('Y-m-d H:i:s', strtotime($due_date));
        }

        $pdo = Database::connect();

        if (!$errors && $dt) {
            $stmt = $pdo->prepare("
                INSERT INTO reminders (user_id, message, due_date)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $message, $dt]);
            $this->redirect('/reminders');
        }

        // Refetch reminders
        $stmt = $pdo->prepare("
            SELECT * FROM reminders
            WHERE user_id = ?
            ORDER BY due_date ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $rows = $stmt->fetchAll();

        $this->view('layout/header', ['title' => 'Reminders']);
        $this->view('users/reminders', [
            'reminders' => $rows,
            'errors' => $errors,
            'message' => $message,
            'due_date' => $due_date
        ]);
        $this->view('layout/footer');
    }

    public function notes() {
        $this->requireLogin();
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT * FROM notes
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $notes = $stmt->fetchAll();

        $this->view('layout/header', ['title' => 'Notes']);
        $this->view('users/notes', ['notes' => $notes]);
        $this->view('layout/footer');
    }

    public function storeNote() {
        $this->requireLogin();
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        $errors = [];

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if ($body === '') {
            $errors[] = "Body is required.";
        }

        $pdo = Database::connect();

        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO notes (user_id, title, body)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $title, $body]);
            $this->redirect('/notes');
        }

        $stmt = $pdo->prepare("
            SELECT * FROM notes
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $notes = $stmt->fetchAll();

        $this->view('layout/header', ['title' => 'Notes']);
        $this->view('users/notes', [
            'notes' => $notes,
            'errors' => $errors,
            'title' => $title,
            'body' => $body
        ]);
        $this->view('layout/footer');
    }

    public function index() {
        $perPage = 20;
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        $pdo = Database::connect();
        $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
        $totalPages = max(1, (int)ceil($total / $perPage));

        $sql = "
            SELECT id, username, is_admin, created_at
            FROM users
            ORDER BY username ASC
            LIMIT $perPage OFFSET $offset
        ";
        $users = $pdo->query($sql)->fetchAll();

        $this->view('layout/header', ['title' => 'Users']);
        $this->view('users/index', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
        $this->view('layout/footer');
    }

    public function posts() {
        $userId = (int)($_GET['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            $this->view('layout/header', ['title' => 'Bad Request']);
            echo '<section class="page"><h1>Bad request</h1></section>';
            $this->view('layout/footer');
            return;
        }

        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, username, is_admin FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            $this->view('layout/header', ['title' => 'User Not Found']);
            echo '<section class="page"><h1>User not found</h1></section>';
            $this->view('layout/footer');
            return;
        }

        // pagination per section
        $perPageProg = 10;
        $perPageMan  = 10;
        $perPageHw   = 10;

        $pageProg = isset($_GET['page_prog']) && (int)$_GET['page_prog'] > 0 ? (int)$_GET['page_prog'] : 1;
        $pageMan  = isset($_GET['page_manual']) && (int)$_GET['page_manual'] > 0 ? (int)$_GET['page_manual'] : 1;
        $pageHw   = isset($_GET['page_hw']) && (int)$_GET['page_hw'] > 0 ? (int)$_GET['page_hw'] : 1;

        $offProg = ($pageProg - 1) * $perPageProg;
        $offMan  = ($pageMan - 1) * $perPageMan;
        $offHw   = ($pageHw - 1) * $perPageHw;

        // Lab programs by user
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_programs WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        $totalProg = (int)$stmt->fetch()['c'];
        $totPagesProg = max(1, (int)ceil($totalProg / $perPageProg));

        $sql = "
            SELECT lp.id, lp.title, lp.created_at,
                   COALESCE(pl.name, lp.language) AS language_name
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            WHERE lp.uploaded_by = ?
            ORDER BY lp.created_at DESC
            LIMIT $perPageProg OFFSET $offProg
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $userPrograms = $stmt->fetchAll();

        // Manuals by user
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_manuals WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        $totalMan = (int)$stmt->fetch()['c'];
        $totPagesMan = max(1, (int)ceil($totalMan / $perPageMan));

        $sql = "
            SELECT id, title, pdf_path, created_at
            FROM lab_manuals
            WHERE uploaded_by = ?
            ORDER BY created_at DESC
            LIMIT $perPageMan OFFSET $offMan
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $userManuals = $stmt->fetchAll();

        // Homework by user
        $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM homework WHERE uploaded_by = ?");
        $stmt->execute([$userId]);
        $totalHw = (int)$stmt->fetch()['c'];
        $totPagesHw = max(1, (int)ceil($totalHw / $perPageHw));

        $sql = "
            SELECT h.id, h.title, h.due_date, h.created_at,
                   COALESCE(s.name, 'Uncategorized') AS subject_name
            FROM homework h
            LEFT JOIN subjects s ON h.subject_id = s.id
            WHERE h.uploaded_by = ?
            ORDER BY h.created_at DESC
            LIMIT $perPageHw OFFSET $offHw
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $userHomework = $stmt->fetchAll();

        $this->view('layout/header', ['title' => 'Posts by ' . $user['username']]);
        $this->view('users/posts', [
            'user' => $user,
            'userPrograms' => $userPrograms,
            'userManuals' => $userManuals,
            'userHomework' => $userHomework,
            'pageProg' => $pageProg,
            'pageMan' => $pageMan,
            'pageHw' => $pageHw,
            'totPagesProg' => $totPagesProg,
            'totPagesMan' => $totPagesMan,
            'totPagesHw' => $totPagesHw
        ]);
        $this->view('layout/footer');
    }
}
