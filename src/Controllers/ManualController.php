<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Utils\Helper;
use App\Models\LabManual;
use RuntimeException;
use finfo;

class ManualController {
    public function index() {
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $manuals = LabManual::all($perPage, $offset);
        $total = LabManual::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        View::render('manuals/index', [
            'manuals' => $manuals,
            'page' => $page,
            'totalPages' => $totalPages,
            'user' => Auth::user(),
            'title' => 'Lab Manuals'
        ]);
    }

    public function new() {
        Auth::requireLogin();
        $user = Auth::user();
        $errors = [];
        $title = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            if ($title === '') {
                $errors[] = "Title is required.";
            }

            $pdfPath = null;
            if (!$errors) {
                try {
                    $pdfPath = $this->handleFileUpload('manual_file');
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors && $pdfPath) {
                LabManual::create($title, $pdfPath, $user['id']);
                Helper::redirect('/manuals');
            }
        }

        View::render('manuals/new', [
            'errors' => $errors,
            'title_val' => $title,
            'user' => $user,
            'title' => 'New Lab Manual'
        ]);
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        $manual = LabManual::find($id);

        if (!$manual) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Manual Not Found']);
            return;
        }

        if (!$user['is_admin'] && (int)$user['id'] !== (int)$manual['uploaded_by']) {
            http_response_code(403);
            die("403 Forbidden");
        }

        $errors = [];
        $title = $manual['title'];
        $pdfPath = $manual['pdf_path'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            if ($title === '') {
                $errors[] = "Title is required.";
            }

            if (!$errors) {
                try {
                    $newPdf = $this->handleFileUpload('manual_file');
                    if ($newPdf !== null) {
                        $pdfPath = $newPdf;
                    }
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                LabManual::update($id, $title, $pdfPath);
                Helper::redirect('/manuals');
            }
        }

        View::render('manuals/edit', [
            'manual' => $manual,
            'errors' => $errors,
            'title_val' => $title,
            'user' => $user,
            'title' => 'Edit Lab Manual'
        ]);
    }

    public function delete() {
        Auth::requireLogin();
        $user = Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $manual = LabManual::find($id);

            if ($manual && ($user['is_admin'] || (int)$user['id'] === (int)$manual['uploaded_by'])) {
                LabManual::delete($id);
            }
        }
        Helper::redirect('/manuals');
    }

    private function handleFileUpload(string $field): ?string {
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

        if ($mime !== 'application/pdf') {
            throw new RuntimeException('Manuals must be PDF.');
        }

        $baseDir = __DIR__ . '/../../public/uploads/manuals';
        $webDir = '/uploads/manuals';

        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        $basename = bin2hex(random_bytes(8)) . '.pdf';
        $targetFs = $baseDir . '/' . $basename;
        $webPath  = $webDir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        return $webPath;
    }
}
