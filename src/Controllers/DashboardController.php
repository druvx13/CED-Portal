<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\CSRF;
use App\Core\View;
use App\Models\LabProgram;
use App\Models\LabManual;
use App\Models\Reminder;

class DashboardController extends BaseController {
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
