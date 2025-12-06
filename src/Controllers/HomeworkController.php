<?php
namespace Controllers;

use Core\Controller;
use Config\Database;
use RuntimeException;
use finfo;

class HomeworkController extends Controller {

    private function requireLogin() {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    private function requireAdmin() {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
            http_response_code(403);
            die('Forbidden');
        }
    }

    private function handleFileUpload($field, $context) {
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$field];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error: ' . (int)$file['error']);
        }

        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new RuntimeException('File too large (max 10MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';

        $ext = '';
        $baseDir = ROOT_PATH . '/public/uploads';
        $webBase = '/uploads';

        if ($context === 'homework_answer') {
            $allowed = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'application/zip' => 'zip',
                'application/x-zip-compressed' => 'zip'
            ];
            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Unsupported answer format.');
            }
            $ext = '.' . $allowed[$mime];
            $targetDir = $baseDir . '/homework_answers';
            $webDir    = $webBase . '/homework_answers';
        } else {
             throw new RuntimeException('Invalid upload context.');
        }

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $basename = bin2hex(random_bytes(8)) . $ext;
        $targetFs = $targetDir . '/' . $basename;
        $webPath  = $webDir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        return $webPath;
    }

    public function index() {
        $perPage = 20;
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        $pdo = Database::connect();
        $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM homework")->fetch()['c'];
        $totalPages = max(1, (int)ceil($total / $perPage));

        $sql = "
            SELECT
                h.*,
                u.username,
                COALESCE(s.name, 'Uncategorized') AS subject_name
            FROM homework h
            LEFT JOIN subjects s ON h.subject_id = s.id
            LEFT JOIN users u ON h.uploaded_by = u.id
            ORDER BY subject_name ASC, h.created_at DESC
            LIMIT $perPage OFFSET $offset
        ";
        $homework = $pdo->query($sql)->fetchAll();

        $grouped = [];
        foreach ($homework as $hw) {
            $grouped[$hw['subject_name']][] = $hw;
        }

        $this->view('layout/header', ['title' => 'Homework']);
        $this->view('homework/index', [
            'grouped' => $grouped,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
        $this->view('layout/footer');
    }

    public function create() {
        $this->requireLogin();
        $this->requireAdmin();

        $pdo = Database::connect();
        $subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetchAll();

        $this->view('layout/header', ['title' => 'Create Homework']);
        $this->view('homework/create', ['subjects' => $subjects]);
        $this->view('layout/footer');
    }

    public function store() {
        $this->requireLogin();
        $this->requireAdmin();
        $pdo = Database::connect();

        $title      = trim($_POST['title'] ?? '');
        $question   = trim($_POST['question'] ?? '');
        $due_date   = trim($_POST['due_date'] ?? '');
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $errors = [];

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if ($question === '') {
            $errors[] = "Question is required.";
        }
        if ($subject_id <= 0) {
            $errors[] = "Subject is required.";
        }

        $dt = null;
        if ($due_date !== '') {
            $dt = date('Y-m-d H:i:s', strtotime($due_date));
        }

        $answerPath = null;
        if (!$errors) {
            try {
                $answerPath = $this->handleFileUpload('answer_file', 'homework_answer');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO homework (title, question, subject_id, due_date, answer_path, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $question,
                $subject_id,
                $dt,
                $answerPath,
                $_SESSION['user_id']
            ]);
            $this->redirect('/homework');
        } else {
             $subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetchAll();
             $this->view('layout/header', ['title' => 'Create Homework']);
             $this->view('homework/create', [
                 'subjects' => $subjects,
                 'errors' => $errors,
                 'title' => $title,
                 'question' => $question,
                 'subject_id' => $subject_id,
                 'due_date' => $due_date
             ]);
             $this->view('layout/footer');
        }
    }

    public function edit() {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM homework WHERE id = ?");
        $stmt->execute([$id]);
        $hw = $stmt->fetch();

        if (!$hw) {
            $this->redirect('/homework');
        }

        if (empty($_SESSION['is_admin']) && (int)$_SESSION['user_id'] !== (int)$hw['uploaded_by']) {
            http_response_code(403);
            die('Forbidden');
        }

        $subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetchAll();

        $this->view('layout/header', ['title' => 'Edit Homework']);
        $this->view('homework/edit', ['hw' => $hw, 'subjects' => $subjects]);
        $this->view('layout/footer');
    }

    public function update() {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM homework WHERE id = ?");
        $stmt->execute([$id]);
        $hw = $stmt->fetch();

        if (!$hw) {
             $this->redirect('/homework');
        }

        if (empty($_SESSION['is_admin']) && (int)$_SESSION['user_id'] !== (int)$hw['uploaded_by']) {
             http_response_code(403);
             die('Forbidden');
        }

        $title      = trim($_POST['title'] ?? '');
        $question   = trim($_POST['question'] ?? '');
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $due_date   = trim($_POST['due_date'] ?? '');
        $errors = [];

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if ($question === '') {
            $errors[] = "Question is required.";
        }
        if ($subject_id <= 0) {
            $errors[] = "Subject is required.";
        }

        $dt = null;
        if ($due_date !== '') {
            $dt = date('Y-m-d H:i:s', strtotime($due_date));
        }

        $answerPath = $hw['answer_path'];

         if (!$errors) {
            try {
                $newAnswer = $this->handleFileUpload('answer_file', 'homework_answer');
                if ($newAnswer !== null) {
                    $answerPath = $newAnswer;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE homework
                SET title = ?, question = ?, subject_id = ?, due_date = ?, answer_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title,
                $question,
                $subject_id,
                $dt,
                $answerPath,
                $id
            ]);
            $this->redirect('/homework');
        } else {
             $subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetchAll();
             $this->view('layout/header', ['title' => 'Edit Homework']);
             $this->view('homework/edit', [
                 'hw' => $hw,
                 'subjects' => $subjects,
                 'errors' => $errors
             ]);
             $this->view('layout/footer');
        }
    }

    public function delete() {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT uploaded_by FROM homework WHERE id = ?");
        $stmt->execute([$id]);
        $hw = $stmt->fetch();

        if ($hw && (!empty($_SESSION['is_admin']) || (int)$_SESSION['user_id'] === (int)$hw['uploaded_by'])) {
            $stmt = $pdo->prepare("DELETE FROM homework WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->redirect('/homework');
    }
}
