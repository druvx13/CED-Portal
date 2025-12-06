<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Utils\Helper;
use App\Models\Homework;
use App\Models\Subject;
use App\Services\UploadService;
use RuntimeException;

class HomeworkController {
    public function index() {
        $perPage = 20;
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        $homework = Homework::all($perPage, $offset);
        $total = Homework::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $grouped = [];
        foreach ($homework as $hw) {
            $grouped[$hw['subject_name']][] = $hw;
        }

        $view = new View();
        $view->render('homework/index', [
            'grouped' => $grouped,
            'page' => $page,
            'totalPages' => $totalPages,
            'currentUser' => Auth::user()
        ], 'Homework');
    }

    public function create() {
        Auth::requireLogin();
        Auth::requireAdmin();
        $user = Auth::user();

        $errors = [];
        $subjects = Subject::all();
        $title = '';
        $question = '';
        $subject_id = 0;
        $due_date = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $question = trim($_POST['question'] ?? '');
            $subject_id = (int)($_POST['subject_id'] ?? 0);
            $due_date = trim($_POST['due_date'] ?? '');

            if ($title === '') $errors[] = "Title is required.";
            if ($question === '') $errors[] = "Question is required.";
            if ($subject_id <= 0) $errors[] = "Subject is required.";

            $dt = null;
            if ($due_date !== '') {
                $dt = date('Y-m-d H:i:s', strtotime($due_date));
            }

            $answerPath = null;
            if (!$errors && !empty($_FILES['answer_file']) && $_FILES['answer_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $answerPath = UploadService::handle($_FILES['answer_file'], UploadService::TYPE_HOMEWORK_ANSWER);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                Homework::create($title, $question, $subject_id, $dt, $answerPath, $user['id']);
                Helper::redirect('/homework');
            }
        }

        $view = new View();
        $view->render('homework/create', [
            'errors' => $errors,
            'subjects' => $subjects,
            'title' => $title,
            'question' => $question,
            'subject_id' => $subject_id,
            'due_date' => $due_date
        ], 'New Homework');
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        $hw = Homework::find($id);

        if (!$hw) {
            http_response_code(404);
            Helper::redirect('/homework');
        }

        if (!$user['is_admin'] && (int)$user['id'] !== (int)$hw['uploaded_by']) {
            http_response_code(403);
            die('Forbidden');
        }

        $subjects = Subject::all();
        $errors = [];
        $title = $hw['title'];
        $question = $hw['question'];
        $subject_id = (int)($hw['subject_id'] ?? 0);
        $due_date = $hw['due_date'] ? date('Y-m-d\TH:i', strtotime($hw['due_date'])) : '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $question = trim($_POST['question'] ?? '');
            $subject_id = (int)($_POST['subject_id'] ?? 0);
            $due_date = trim($_POST['due_date'] ?? '');

            if ($title === '') $errors[] = "Title is required.";
            if ($question === '') $errors[] = "Question is required.";
            if ($subject_id <= 0) $errors[] = "Subject is required.";

            $dt = null;
            if ($due_date !== '') {
                $dt = date('Y-m-d H:i:s', strtotime($due_date));
            }

            $answerPath = $hw['answer_path'];
            if (!$errors && !empty($_FILES['answer_file']) && $_FILES['answer_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                try {
                    $answerPath = UploadService::handle($_FILES['answer_file'], UploadService::TYPE_HOMEWORK_ANSWER);
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                Homework::update($id, $title, $question, $subject_id, $dt, $answerPath);
                Helper::redirect('/homework');
            }
        }

        $view = new View();
        $view->render('homework/edit', [
            'id' => $id,
            'title' => $title,
            'question' => $question,
            'subject_id' => $subject_id,
            'due_date' => $due_date,
            'subjects' => $subjects,
            'errors' => $errors
        ], 'Edit Homework');
    }

    public function delete() {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $id = (int)($_POST['id'] ?? 0);
             $hw = Homework::find($id);
             $user = Auth::user();

             if ($hw && ($user['is_admin'] || (int)$user['id'] === (int)$hw['uploaded_by'])) {
                 Homework::delete($id);
             }
             Helper::redirect('/homework');
        }
    }

}
