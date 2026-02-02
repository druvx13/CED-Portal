<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Core\CSRF;
use App\Core\View;
use App\Utils\Helper;
use App\Models\User;
use App\Models\LabProgram;
use App\Models\LabManual;
use App\Models\Homework;

class UserController extends BaseController {
    public function index() {
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $users = User::all($perPage, $offset);
        $total = User::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        View::render('users/index', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'user' => Auth::user(),
            'title' => 'Users'
        ]);
    }

    public function posts() {
        $userId = (int)($_GET['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            View::render('errors/400', ['title' => 'Bad Request']);
            return;
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'User Not Found']);
            return;
        }

        $perPageProg = 10;
        $perPageMan  = 10;
        $perPageHw   = 10;

        $pageProg = isset($_GET['page_prog']) && (int)$_GET['page_prog'] > 0 ? (int)$_GET['page_prog'] : 1;
        $pageMan  = isset($_GET['page_manual']) && (int)$_GET['page_manual'] > 0 ? (int)$_GET['page_manual'] : 1;
        $pageHw   = isset($_GET['page_hw']) && (int)$_GET['page_hw'] > 0 ? (int)$_GET['page_hw'] : 1;

        $offProg = ($pageProg - 1) * $perPageProg;
        $offMan  = ($pageMan - 1) * $perPageMan;
        $offHw   = ($pageHw - 1) * $perPageHw;

        $userPrograms = LabProgram::getByUser($userId, $perPageProg, $offProg);
        $totalProg = LabProgram::countByUser($userId);
        $totPagesProg = max(1, (int)ceil($totalProg / $perPageProg));

        $userManuals = LabManual::getByUser($userId, $perPageMan, $offMan);
        $totalMan = LabManual::countByUser($userId);
        $totPagesMan = max(1, (int)ceil($totalMan / $perPageMan));

        $userHomework = Homework::getByUser($userId, $perPageHw, $offHw);
        $totalHw = Homework::countByUser($userId);
        $totPagesHw = max(1, (int)ceil($totalHw / $perPageHw));

        View::render('users/posts', [
            'targetUser' => $targetUser,
            'userPrograms' => $userPrograms,
            'totPagesProg' => $totPagesProg,
            'pageProg' => $pageProg,
            'userManuals' => $userManuals,
            'totPagesMan' => $totPagesMan,
            'pageMan' => $pageMan,
            'userHomework' => $userHomework,
            'totPagesHw' => $totPagesHw,
            'pageHw' => $pageHw,
            'user' => Auth::user(),
            'title' => 'Posts by ' . $targetUser['username']
        ]);
    }
}
