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
use App\Models\Reminder;

class ReminderController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $message = '';
        $due_date = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message  = trim($_POST['message'] ?? '');
            $due_date = trim($_POST['due_date'] ?? '');

            if ($message === '') {
                $errors[] = "Message is required.";
            }
            if ($due_date === '') {
                $errors[] = "Due date is required.";
            }

            $dt = null;
            if ($due_date !== '') {
                $dt = date('Y-m-d H:i:s', strtotime($due_date));
            }

            if (!$errors && $dt) {
                Reminder::create($user['id'], $message, $dt);
                Helper::redirect('/reminders');
            }
        }

        $reminders = Reminder::allForUser($user['id']);

        View::render('reminders/index', [
            'reminders' => $reminders,
            'errors' => $errors,
            'message_val' => $message,
            'due_date_val' => $due_date,
            'user' => $user,
            'title' => 'Reminders'
        ]);
    }
}
