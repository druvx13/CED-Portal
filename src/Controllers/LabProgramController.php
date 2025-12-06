<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Utils\Helper;
use App\Models\LabProgram;
use App\Models\Language;
use App\Services\UploadService;
use RuntimeException;

class LabProgramController {
    public function index() {
        $perPage = 20;
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        $programs = LabProgram::all($perPage, $offset);
        $total = LabProgram::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $grouped = [];
        foreach ($programs as $r) {
            $grouped[$r['language_name']][] = $r;
        }

        $view = new View();
        $view->render('lab_programs/index', [
            'grouped' => $grouped,
            'page' => $page,
            'totalPages' => $totalPages,
            'currentUser' => Auth::user()
        ], 'Lab Programs');
    }

    public function create() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $title = '';
        $code = '';
        $language_id = 0;

        $languages = Language::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $code = (string)($_POST['code'] ?? '');
            $language_id = (int)($_POST['language_id'] ?? 0);

            if ($title === '') $errors[] = "Title is required.";
            if (mb_strlen($code, 'UTF-8') < 5) $errors[] = "Code too short.";
            if ($language_id <= 0) $errors[] = "Language must be selected.";

            $chosenLangSlug = null;
            foreach ($languages as $l) {
                if ((int)$l['id'] === $language_id) {
                    $chosenLangSlug = $l['slug'];
                    break;
                }
            }

            $outputPath = null;
            if (!$errors && !empty($_FILES['output_file']) && $_FILES['output_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $outputPath = UploadService::handle($_FILES['output_file'], UploadService::TYPE_CODE_OUTPUT);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                LabProgram::create($title, $code, $chosenLangSlug ?: 'unknown', $language_id, $outputPath, $user['id']);
                Helper::redirect('/lab-programs');
            }
        }

        $view = new View();
        $view->render('lab_programs/create', [
            'errors' => $errors,
            'languages' => $languages,
            'title' => $title,
            'code' => $code,
            'language_id' => $language_id
        ], 'New Lab Program');
    }

    public function view() {
        $id = (int)($_GET['id'] ?? 0);
        $program = LabProgram::find($id);

        if (!$program) {
            http_response_code(404);
            $view = new View();
            $view->render('errors/404', [], 'Not Found');
            exit;
        }

        $view = new View();
        $view->render('lab_programs/view', [
            'p' => $program,
            'currentUser' => Auth::user()
        ], 'Lab Program - ' . $program['title']);
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        $program = LabProgram::find($id);

        if (!$program) {
            http_response_code(404);
            Helper::redirect('/lab-programs');
        }

        if (!$user['is_admin'] && (int)$user['id'] !== (int)$program['uploaded_by']) {
            http_response_code(403);
            die('Forbidden');
        }

        $errors = [];
        $languages = Language::all();

        $title = $program['title'];
        $code = $program['code'];
        $language_id = (int)($program['language_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $title = trim($_POST['title'] ?? '');
             $code = (string)($_POST['code'] ?? '');
             $language_id = (int)($_POST['language_id'] ?? 0);

             if ($title === '') $errors[] = "Title is required.";
             if (mb_strlen($code, 'UTF-8') < 5) $errors[] = "Code too short.";
             if ($language_id <= 0) $errors[] = "Language must be selected.";

             $chosenLangSlug = null;
             foreach ($languages as $l) {
                 if ((int)$l['id'] === $language_id) {
                     $chosenLangSlug = $l['slug'];
                     break;
                 }
             }

             $outputPath = $program['output_path'];
             if (!$errors && !empty($_FILES['output_file']) && $_FILES['output_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                 try {
                     $outputPath = UploadService::handle($_FILES['output_file'], UploadService::TYPE_CODE_OUTPUT);
                 } catch (RuntimeException $e) {
                     $errors[] = $e->getMessage();
                 }
             }

             if (!$errors) {
                 LabProgram::update($id, $title, $code, $chosenLangSlug ?: 'unknown', $language_id, $outputPath);
                 Helper::redirect('/lab-programs/view?id=' . $id);
             }
        }

        $view = new View();
        $view->render('lab_programs/edit', [
            'id' => $id,
            'title' => $title,
            'code' => $code,
            'language_id' => $language_id,
            'languages' => $languages,
            'errors' => $errors
        ], 'Edit Lab Program');
    }

    public function delete() {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $id = (int)($_POST['id'] ?? 0);
             $program = LabProgram::find($id);
             $user = Auth::user();

             if ($program && ($user['is_admin'] || (int)$user['id'] === (int)$program['uploaded_by'])) {
                 LabProgram::delete($id);
             }
             Helper::redirect('/lab-programs');
        }
    }

}
