<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Utils\Helper;
use App\Models\LabManual;
use App\Services\UploadService;
use RuntimeException;

class ManualController {
    public function index() {
        $perPage = 20;
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        $manuals = LabManual::all($perPage, $offset);
        $total = LabManual::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $view = new View();
        $view->render('manuals/index', [
            'manuals' => $manuals,
            'page' => $page,
            'totalPages' => $totalPages,
            'currentUser' => Auth::user()
        ], 'Lab Manuals');
    }

    public function create() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $title = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            if ($title === '') $errors[] = "Title is required.";

            $pdfPath = null;
            if (!$errors) {
                if (empty($_FILES['manual_file']) || $_FILES['manual_file']['error'] === UPLOAD_ERR_NO_FILE) {
                     $errors[] = "PDF file is required.";
                } else {
                    try {
                        $pdfPath = UploadService::handle($_FILES['manual_file'], UploadService::TYPE_MANUAL);
                    } catch (RuntimeException $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }

            if (!$errors && $pdfPath) {
                LabManual::create($title, $pdfPath, $user['id']);
                Helper::redirect('/manuals');
            }
        }

        $view = new View();
        $view->render('manuals/create', [
            'errors' => $errors,
            'title' => $title
        ], 'New Lab Manual');
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        $manual = LabManual::find($id);

        if (!$manual) {
            http_response_code(404);
            Helper::redirect('/manuals');
        }

        if (!$user['is_admin'] && (int)$user['id'] !== (int)$manual['uploaded_by']) {
            http_response_code(403);
            die('Forbidden');
        }

        $errors = [];
        $title = $manual['title'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            if ($title === '') $errors[] = "Title is required.";

            $pdfPath = $manual['pdf_path'];
            if (!$errors && !empty($_FILES['manual_file']) && $_FILES['manual_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $pdfPath = UploadService::handle($_FILES['manual_file'], UploadService::TYPE_MANUAL);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                LabManual::update($id, $title, $pdfPath);
                Helper::redirect('/manuals');
            }
        }

        $view = new View();
        $view->render('manuals/edit', [
            'id' => $id,
            'title' => $title,
            'errors' => $errors
        ], 'Edit Lab Manual');
    }

    public function delete() {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $id = (int)($_POST['id'] ?? 0);
             $manual = LabManual::find($id);
             $user = Auth::user();

             if ($manual && ($user['is_admin'] || (int)$user['id'] === (int)$manual['uploaded_by'])) {
                 LabManual::delete($id);
             }
             Helper::redirect('/manuals');
        }
    }

}
