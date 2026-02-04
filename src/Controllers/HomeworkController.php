<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Utils\Helper;
use App\Models\Homework;
use App\Models\Subject;
use RuntimeException;
use finfo;

class HomeworkController {
    public function index() {
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $homework = Homework::all($perPage, $offset);
        $total = Homework::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $grouped = [];
        foreach ($homework as $hw) {
            $grouped[$hw['subject_name']][] = $hw;
        }

        View::render('homework/index', [
            'grouped' => $grouped,
            'page' => $page,
            'totalPages' => $totalPages,
            'user' => Auth::user(),
            'title' => 'Homework'
        ]);
    }

    public function new() {
        Auth::requireLogin();
        // Prompt says "Only staff can post new ones", assuming Admin check for now based on original code
        Auth::requireAdmin();
        $user = Auth::user();

        $errors = [];
        $title = $question = '';
        $due_date = '';
        $subject_id = 0;

        $subjects = Subject::all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title      = trim($_POST['title'] ?? '');
            $question   = trim($_POST['question'] ?? '');
            $due_date   = trim($_POST['due_date'] ?? '');
            $subject_id = (int)($_POST['subject_id'] ?? 0);

            if ($title === '') $errors[] = "Title is required.";
            if ($question === '') $errors[] = "Question is required.";
            if ($subject_id <= 0) $errors[] = "Subject is required.";

            $dt = null;
            if ($due_date !== '') {
                $dt = date('Y-m-d H:i:s', strtotime($due_date));
            }

            $answerPath = null;
            if (!$errors) {
                try {
                    $answerPath = $this->handleFileUpload('answer_file');
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                Homework::create([
                    'title' => $title,
                    'question' => $question,
                    'subject_id' => $subject_id,
                    'due_date' => $dt,
                    'answer_path' => $answerPath,
                    'uploaded_by' => $user['id']
                ]);
                Helper::redirect('/homework');
            }
        }

        View::render('homework/new', [
            'subjects' => $subjects,
            'errors' => $errors,
            'title_val' => $title,
            'question_val' => $question,
            'due_date_val' => $due_date,
            'subject_id_val' => $subject_id,
            'user' => $user,
            'title' => 'New Homework'
        ]);
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        $hw = Homework::find($id);

        if (!$hw) {
            http_response_code(404);
             View::render('errors/404', ['title' => 'Homework Not Found']);
            return;
        }

        if (!$user['is_admin'] && (int)$user['id'] !== (int)$hw['uploaded_by']) {
            http_response_code(403);
            die("403 Forbidden");
        }

        $subjects = Subject::all();

        $errors = [];
        $title = $hw['title'];
        $question = $hw['question'];
        $subject_id = (int)($hw['subject_id'] ?? 0);
        $due_date = $hw['due_date'] ? date('Y-m-d\TH:i', strtotime($hw['due_date'])) : '';
        $answerPath = $hw['answer_path'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $title      = trim($_POST['title'] ?? '');
            $question   = trim($_POST['question'] ?? '');
            $subject_id = (int)($_POST['subject_id'] ?? 0);
            $due_date   = trim($_POST['due_date'] ?? '');

            if ($title === '') $errors[] = "Title is required.";
            if ($question === '') $errors[] = "Question is required.";
            if ($subject_id <= 0) $errors[] = "Subject is required.";

            $dt = null;
            if ($due_date !== '') {
                $dt = date('Y-m-d H:i:s', strtotime($due_date));
            }

             if (!$errors) {
                try {
                    $newAnswer = $this->handleFileUpload('answer_file');
                    if ($newAnswer !== null) {
                        $answerPath = $newAnswer;
                    }
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!$errors) {
                Homework::update($id, [
                    'title' => $title,
                    'question' => $question,
                    'subject_id' => $subject_id,
                    'due_date' => $dt,
                    'answer_path' => $answerPath
                ]);
                 Helper::redirect('/homework');
            }
        }

        View::render('homework/edit', [
            'hw' => $hw,
            'subjects' => $subjects,
            'errors' => $errors,
             'title_val' => $title,
            'question_val' => $question,
            'due_date_val' => $due_date,
            'subject_id_val' => $subject_id,
            'user' => $user,
            'title' => 'Edit Homework'
        ]);
    }

    public function delete() {
        Auth::requireLogin();
        $user = Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $hw = Homework::find($id);

            if ($hw && ($user['is_admin'] || (int)$user['id'] === (int)$hw['uploaded_by'])) {
                Homework::delete($id);
            }
        }
        Helper::redirect('/homework');
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
        $baseDir = __DIR__ . '/../../public/uploads/homework_answers';
        $webDir = '/uploads/homework_answers';

        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        $basename = bin2hex(random_bytes(8)) . $ext;
        $targetFs = $baseDir . '/' . $basename;
        $webPath  = $webDir . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        return $webPath;
    }
}
