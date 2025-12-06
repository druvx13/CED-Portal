<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
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

        $view = new View();
        $view->render('dashboard/index', [
            'currentUser' => $user,
            'reminders' => $reminders,
            'programs' => $programs,
            'manuals' => $manuals
        ], 'Dashboard');
    }
}
