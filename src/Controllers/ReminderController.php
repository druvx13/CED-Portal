<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Utils\Helper;
use App\Models\Reminder;

class ReminderController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $message = '';
        $due_date = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = trim($_POST['message'] ?? '');
            $due_date = trim($_POST['due_date'] ?? '');

            if ($message === '') $errors[] = "Message is required.";
            if ($due_date === '') $errors[] = "Due date is required.";

            $dt = null;
            if ($due_date !== '') {
                $dt = date('Y-m-d H:i:s', strtotime($due_date));
            }

            if (!$errors && $dt) {
                Reminder::create($user['id'], $message, $dt);
                Helper::redirect('/reminders');
            }
        }

        $reminders = Reminder::getAllForUser($user['id']);

        $view = new View();
        $view->render('reminders/index', [
            'errors' => $errors,
            'message' => $message,
            'due_date' => $due_date,
            'reminders' => $reminders
        ], 'Reminders');
    }
}
