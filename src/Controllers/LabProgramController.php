<?php
namespace Controllers;

use Core\Controller;
use Config\Database;
use RuntimeException;
use finfo;

class LabProgramController extends Controller {

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

        if ($context === 'code_output') {
            $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'application/pdf' => 'pdf'];
            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Invalid output file type.');
            }
            $ext = '.' . $allowed[$mime];
            $targetDir = $baseDir . '/code_outputs';
            $webDir    = $webBase . '/code_outputs';
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
        $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM lab_programs")->fetch()['c'];
        $totalPages = max(1, (int)ceil($total / $perPage));

        $sql = "
            SELECT
                lp.id,
                lp.title,
                lp.code,
                lp.created_at,
                lp.uploaded_by,
                u.username,
                COALESCE(pl.name, lp.language) AS language_name,
                COALESCE(pl.slug, lp.language) AS language_slug
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            LEFT JOIN users u ON lp.uploaded_by = u.id
            ORDER BY language_name ASC, lp.created_at DESC
            LIMIT $perPage OFFSET $offset
        ";
        $rows = $pdo->query($sql)->fetchAll();

        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['language_name']][] = $r;
        }

        $this->view('layout/header', ['title' => 'Lab Programs']);
        $this->view('lab-programs/index', [
            'grouped' => $grouped,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
        $this->view('layout/footer');
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT
                lp.*,
                u.username,
                COALESCE(pl.name, lp.language) AS language_name,
                COALESCE(pl.slug, lp.language) AS language_slug
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            LEFT JOIN users u ON lp.uploaded_by = u.id
            WHERE lp.id = ?
        ");
        $stmt->execute([$id]);
        $p = $stmt->fetch();

        if (!$p) {
             http_response_code(404);
             $this->view('layout/header', ['title' => 'Not Found']);
             $this->view('404');
             $this->view('layout/footer');
             return;
        }

        $this->view('layout/header', ['title' => $p['title']]);
        $this->view('lab-programs/show', ['p' => $p]);
        $this->view('layout/footer');
    }

    public function create() {
        $this->requireLogin();
        $pdo = Database::connect();
        $languages = $pdo->query("SELECT id, name, slug FROM programming_languages ORDER BY name ASC")->fetchAll();

        $this->view('layout/header', ['title' => 'New Lab Program']);
        $this->view('lab-programs/create', ['languages' => $languages]);
        $this->view('layout/footer');
    }

    public function store() {
        $this->requireLogin();
        $pdo = Database::connect();

        $title       = trim($_POST['title'] ?? '');
        $code        = (string)($_POST['code'] ?? '');
        $language_id = (int)($_POST['language_id'] ?? 0);
        $errors = [];

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if (mb_strlen($code, 'UTF-8') < 5) {
            $errors[] = "Code too short.";
        }
        if ($language_id <= 0) {
            $errors[] = "Language must be selected.";
        }

        $languages = $pdo->query("SELECT id, name, slug FROM programming_languages ORDER BY name ASC")->fetchAll();

        $chosenLangSlug = null;
        if ($language_id > 0) {
            foreach ($languages as $l) {
                if ((int)$l['id'] === $language_id) {
                    $chosenLangSlug = $l['slug'];
                    break;
                }
            }
        }

        $outputPath = null;
        if (!$errors) {
            try {
                $outputPath = $this->handleFileUpload('output_file', 'code_output');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $storedLanguageString = $chosenLangSlug ?: 'unknown';
            $stmt = $pdo->prepare("
                INSERT INTO lab_programs (title, code, language, language_id, output_path, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $code,
                $storedLanguageString,
                $language_id,
                $outputPath,
                $_SESSION['user_id']
            ]);
            $this->redirect('/lab-programs');
        } else {
             $this->view('layout/header', ['title' => 'New Lab Program']);
             $this->view('lab-programs/create', [
                 'languages' => $languages,
                 'errors' => $errors,
                 'title' => $title,
                 'code' => $code,
                 'language_id' => $language_id
             ]);
             $this->view('layout/footer');
        }
    }

    public function edit() {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM lab_programs WHERE id = ?");
        $stmt->execute([$id]);
        $program = $stmt->fetch();

        if (!$program) {
            $this->redirect('/lab-programs');
        }

        if (empty($_SESSION['is_admin']) && (int)$_SESSION['user_id'] !== (int)$program['uploaded_by']) {
            http_response_code(403);
            die('Forbidden');
        }

        $languages = $pdo->query("SELECT id, name, slug FROM programming_languages ORDER BY name ASC")->fetchAll();

        $this->view('layout/header', ['title' => 'Edit Lab Program']);
        $this->view('lab-programs/edit', ['program' => $program, 'languages' => $languages]);
        $this->view('layout/footer');
    }

    public function update() {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM lab_programs WHERE id = ?");
        $stmt->execute([$id]);
        $program = $stmt->fetch();

        if (!$program) {
             $this->redirect('/lab-programs');
        }

        if (empty($_SESSION['is_admin']) && (int)$_SESSION['user_id'] !== (int)$program['uploaded_by']) {
             http_response_code(403);
             die('Forbidden');
        }

        $title       = trim($_POST['title'] ?? '');
        $code        = (string)($_POST['code'] ?? '');
        $language_id = (int)($_POST['language_id'] ?? 0);
        $errors = [];

        $languages = $pdo->query("SELECT id, name, slug FROM programming_languages ORDER BY name ASC")->fetchAll();

        $chosenLangSlug = null;
        if ($language_id > 0) {
            foreach ($languages as $l) {
                if ((int)$l['id'] === $language_id) {
                    $chosenLangSlug = $l['slug'];
                    break;
                }
            }
        }

        $outputPath = $program['output_path'];

         if (!$errors) {
            try {
                $newOutput = $this->handleFileUpload('output_file', 'code_output');
                if ($newOutput !== null) {
                    $outputPath = $newOutput;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $storedLanguageString = $chosenLangSlug ?: $program['language'];
            $stmt = $pdo->prepare("
                UPDATE lab_programs
                SET title = ?, code = ?, language = ?, language_id = ?, output_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title,
                $code,
                $storedLanguageString,
                $language_id,
                $outputPath,
                $id
            ]);
            $this->redirect('/lab-programs/view?id=' . $id);
        } else {
             $this->view('layout/header', ['title' => 'Edit Lab Program']);
             $this->view('lab-programs/edit', [
                 'program' => $program,
                 'languages' => $languages,
                 'errors' => $errors
             ]);
             $this->view('layout/footer');
        }
    }

    public function delete() {
        $this->requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT uploaded_by FROM lab_programs WHERE id = ?");
        $stmt->execute([$id]);
        $program = $stmt->fetch();

        if ($program && (!empty($_SESSION['is_admin']) || (int)$_SESSION['user_id'] === (int)$program['uploaded_by'])) {
            $stmt = $pdo->prepare("DELETE FROM lab_programs WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->redirect('/lab-programs');
    }
}
