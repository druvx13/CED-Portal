<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\CSRF;
use App\Core\View;
use App\Utils\Helper;
use App\Models\Note;

class NoteController extends BaseController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $title = $body = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::requireToken();
            $title = trim($_POST['title'] ?? '');
            $body  = trim($_POST['body'] ?? '');

            if ($title === '') {
                $errors[] = "Title is required.";
            }
            if ($body === '') {
                $errors[] = "Body is required.";
            }

            if (!$errors) {
                Note::create($user['id'], $title, $body);
                Helper::redirect('/notes');
            }
        }

        $notes = Note::allForUser($user['id']);

        View::render('notes/index', [
            'notes' => $notes,
            'errors' => $errors,
            'title_val' => $title,
            'body_val' => $body,
            'user' => $user,
            'title' => 'Notes'
        ]);
    }
}
