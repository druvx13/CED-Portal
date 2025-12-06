<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\User;
use App\Models\LabProgram;
use App\Models\LabManual;
use App\Models\Homework;

class UserController {
    public function index() {
        $perPage = 20;
        $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;

        $users = User::all($perPage, $offset);
        $total = User::count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $view = new View();
        $view->render('users/index', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages
        ], 'Users');
    }

    public function posts() {
        $userId = (int)($_GET['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            die("Bad Request");
        }

        $user = User::find($userId);
        if (!$user) {
            http_response_code(404);
            die("User not found");
        }

        $perPage = 10;

        $pageProg = isset($_GET['page_prog']) && (int)$_GET['page_prog'] > 0 ? (int)$_GET['page_prog'] : 1;
        $pageMan  = isset($_GET['page_manual']) && (int)$_GET['page_manual'] > 0 ? (int)$_GET['page_manual'] : 1;
        $pageHw   = isset($_GET['page_hw']) && (int)$_GET['page_hw'] > 0 ? (int)$_GET['page_hw'] : 1;

        $offProg = ($pageProg - 1) * $perPage;
        $offMan  = ($pageMan - 1) * $perPage;
        $offHw   = ($pageHw - 1) * $perPage;

        $userPrograms = LabProgram::findByUserId($userId, $perPage, $offProg);
        $totPagesProg = max(1, (int)ceil(LabProgram::countByUserId($userId) / $perPage));

        $userManuals = LabManual::findByUserId($userId, $perPage, $offMan);
        $totPagesMan = max(1, (int)ceil(LabManual::countByUserId($userId) / $perPage));

        $userHomework = Homework::findByUserId($userId, $perPage, $offHw);
        $totPagesHw = max(1, (int)ceil(Homework::countByUserId($userId) / $perPage));

        $view = new View();
        $view->render('users/posts', [
            'user' => $user,
            'userPrograms' => $userPrograms,
            'pageProg' => $pageProg,
            'totPagesProg' => $totPagesProg,
            'userManuals' => $userManuals,
            'pageMan' => $pageMan,
            'totPagesMan' => $totPagesMan,
            'userHomework' => $userHomework,
            'pageHw' => $pageHw,
            'totPagesHw' => $totPagesHw
        ], 'Posts by ' . $user['username']);
    }
}
