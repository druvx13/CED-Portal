<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\LabProgram;
use App\Models\LabManual;
use App\Models\Reminder;

class DashboardController {
    public function index() {
        $user = Auth::user();

        $reminders = [];
        if ($user) {
            $reminders = Reminder::getUpcomingForUser($user['id']);
        }

        $programs = LabProgram::getRecent(5);
        $manuals = LabManual::getRecent(3);

        View::render('dashboard/index', [
            'user' => $user,
            'reminders' => $reminders,
            'programs' => $programs,
            'manuals' => $manuals,
            'title' => 'Dashboard'
        ]);
    }
}
