<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Utils\Helper;
use App\Models\LabProgram;
use App\Models\Language;
use RuntimeException;
use finfo;

class LabProgramController {
    public function index() {
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $programs = LabProgram::all($perPage, $offset);
        $total = LabProgram::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $grouped = [];
        foreach ($programs as $r) {
            $grouped[$r['language_name']][] = $r;
        }

        View::render('lab_programs/index', [
            'grouped' => $grouped,
            'page' => $page,
            'totalPages' => $totalPages,
            'user' => Auth::user(),
            'title' => 'Lab Programs'
        ]);
    }

    public function view() {
        $id = (int)($_GET['id'] ?? 0);
        $program = LabProgram::find($id);

        if (!$program) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Program Not Found']);
            return;
        }

        View::render('lab_programs/view', [
            'program' => $program,
            'user' => Auth::user(),
            'title' => 'Lab Program - ' . $program['title']
        ]);
    }

    public function new() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $title = '';
        $code = '';
        $language_id = 0;

        $languages = Language::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title       = trim($_POST['title'] ?? '');
            $code        = (string)($_POST['code'] ?? '');
            $language_id = (int)($_POST['language_id'] ?? 0);

            if ($title === '') $errors[] = "Title is required.";
            if (mb_strlen($code, 'UTF-8') < 5) $errors[] = "Code too short.";
            if ($language_id <= 0) $errors[] = "Language must be selected.";

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
                LabProgram::create([
                    'title' => $title,
                    'code' => $code,
                    'language' => $chosenLangSlug ?: 'unknown',
                    'language_id' => $language_id,
                    'output_path' => $outputPath,
                    'uploaded_by' => $user['id']
                ]);
                Helper::redirect('/lab-programs');
            }
        }

        View::render('lab_programs/new', [
            'languages' => $languages,
            'errors' => $errors,
            'title_val' => $title,
            'code_val' => $code,
            'language_id_val' => $language_id,
            'user' => $user,
            'title' => 'New Lab Program'
        ]);
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        $program = LabProgram::find($id);

        if (!$program) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Program Not Found']);
            return;
        }

        if (!$user['is_admin'] && (int)$user['id'] !== (int)$program['uploaded_by']) {
            http_response_code(403);
            die("403 Forbidden");
        }

        $errors = [];
        $title = $program['title'];
        $code = $program['code'];
        $language_id = (int)($program['language_id'] ?? 0);
        $outputPath = $program['output_path'];

        $languages = Language::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $title       = trim($_POST['title'] ?? '');
            $code        = (string)($_POST['code'] ?? '');
            $language_id = (int)($_POST['language_id'] ?? 0);

            if ($title === '') $errors[] = "Title is required.";
            if (mb_strlen($code, 'UTF-8') < 5) $errors[] = "Code too short.";
            if ($language_id <= 0) $errors[] = "Language must be selected.";

             $chosenLangSlug = null;
            if ($language_id > 0) {
                foreach ($languages as $l) {
                    if ((int)$l['id'] === $language_id) {
                        $chosenLangSlug = $l['slug'];
                        break;
                    }
                }
            }

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
                LabProgram::update($id, [
                    'title' => $title,
                    'code' => $code,
                    'language' => $chosenLangSlug ?: $program['language'],
                    'language_id' => $language_id,
                    'output_path' => $outputPath
                ]);
                Helper::redirect('/lab-programs/view?id=' . $id);
            }
        }

        View::render('lab_programs/edit', [
            'program' => $program,
            'languages' => $languages,
            'errors' => $errors,
             'title_val' => $title,
            'code_val' => $code,
            'language_id_val' => $language_id,
            'user' => $user,
            'title' => 'Edit Lab Program'
        ]);
    }

    public function delete() {
        Auth::requireLogin();
        $user = Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $program = LabProgram::find($id);

            if ($program && ($user['is_admin'] || (int)$user['id'] === (int)$program['uploaded_by'])) {
                LabProgram::delete($id);
            }
        }
        Helper::redirect('/lab-programs');
    }

    private function handleFileUpload(string $field, string $context): ?string {
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

        $baseDir = __DIR__ . '/../../public/uploads';
        $webBase = '/uploads';

        // Ensure uploads directory exists
        if (!is_dir($baseDir)) {
             @mkdir($baseDir, 0755, true);
        }

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
}
