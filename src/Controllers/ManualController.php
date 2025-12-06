<?php
namespace Controllers;

use Core\Controller;
use Config\Database;
use RuntimeException;
use finfo;

class ManualController extends Controller {

    private function requireLogin() {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
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

        if ($context === 'manual') {
            if ($mime !== 'application/pdf') {
                throw new RuntimeException('Manuals must be PDF.');
            }
            $ext = '.pdf';
            $targetDir = $baseDir . '/manuals';
            $webDir    = $webBase . '/manuals';
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
        $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM lab_manuals")->fetch()['c'];
        $totalPages = max(1, (int)ceil($total / $perPage));

        $sql = "
            SELECT lm.*, u.username
            FROM lab_manuals lm
            LEFT JOIN users u ON lm.uploaded_by = u.id
            ORDER BY lm.created_at DESC
            LIMIT $perPage OFFSET $offset
        ";
        $manuals = $pdo->query($sql)->fetchAll();

        $this->view('layout/header', ['title' => 'Lab Manuals']);
        $this->view('manuals/index', [
            'manuals' => $manuals,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
        $this->view('layout/footer');
    }

    public function create() {
        $this->requireLogin();
        $this->view('layout/header', ['title' => 'New Lab Manual']);
        $this->view('manuals/create');
        $this->view('layout/footer');
    }

    public function store() {
        $this->requireLogin();
        $pdo = Database::connect();

        $title = trim($_POST['title'] ?? '');
        $errors = [];

        if ($title === '') {
            $errors[] = "Title is required.";
        }

        $pdfPath = null;
        if (!$errors) {
            try {
                $pdfPath = $this->handleFileUpload('manual_file', 'manual');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors && $pdfPath) {
            $stmt = $pdo->prepare("
                INSERT INTO lab_manuals (title, pdf_path, uploaded_by)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$title, $pdfPath, $_SESSION['user_id']]);
            $this->redirect('/manuals');
        } else {
             $this->view('layout/header', ['title' => 'New Lab Manual']);
             $this->view('manuals/create', [
                 'errors' => $errors,
                 'title' => $title
             ]);
             $this->view('layout/footer');
        }
    }

    public function edit() {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
        $manual = $stmt->fetch();

        if (!$manual) {
            $this->redirect('/manuals');
        }

        if (empty($_SESSION['is_admin']) && (int)$_SESSION['user_id'] !== (int)$manual['uploaded_by']) {
            http_response_code(403);
            die('Forbidden');
        }

        $this->view('layout/header', ['title' => 'Edit Lab Manual']);
        $this->view('manuals/edit', ['manual' => $manual]);
        $this->view('layout/footer');
    }

    public function update() {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
        $manual = $stmt->fetch();

        if (!$manual) {
             $this->redirect('/manuals');
        }

        if (empty($_SESSION['is_admin']) && (int)$_SESSION['user_id'] !== (int)$manual['uploaded_by']) {
             http_response_code(403);
             die('Forbidden');
        }

        $title = trim($_POST['title'] ?? '');
        $errors = [];
        if ($title === '') {
            $errors[] = "Title is required.";
        }

        $pdfPath = $manual['pdf_path'];

         if (!$errors) {
            try {
                $newPdf = $this->handleFileUpload('manual_file', 'manual');
                if ($newPdf !== null) {
                    $pdfPath = $newPdf;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE lab_manuals
                SET title = ?, pdf_path = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $pdfPath, $id]);
            $this->redirect('/manuals');
        } else {
             $this->view('layout/header', ['title' => 'Edit Lab Manual']);
             $this->view('manuals/edit', [
                 'manual' => $manual,
                 'errors' => $errors
             ]);
             $this->view('layout/footer');
        }
    }

    public function delete() {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT uploaded_by FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
        $manual = $stmt->fetch();

        if ($manual && (!empty($_SESSION['is_admin']) || (int)$_SESSION['user_id'] === (int)$manual['uploaded_by'])) {
            $stmt = $pdo->prepare("DELETE FROM lab_manuals WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->redirect('/manuals');
    }
}
