<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Language;
use App\Models\Subject;
use App\Utils\Helper;
use Throwable;

class AdminController {
    public function users() {
        Auth::requireLogin();
        Auth::requireAdmin();
        $currentUser = Auth::user();

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Require superadmin
            if (!$currentUser || (int)$currentUser['id'] !== 1 || !(int)$currentUser['is_first_admin']) {
                http_response_code(403);
                die("Forbidden: Only initial admin can create users.");
            }

            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $is_admin = !empty($_POST['is_admin']);

            if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters.";
            if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";

            if (!$errors) {
                if (User::findByUsername($username)) {
                    $errors[] = "Username already taken.";
                } else {
                    try {
                        $newId = User::create($username, $password, $is_admin, $currentUser['id']);
                        $success = "User created (ID: {$newId}).";
                    } catch (Throwable $e) {
                        $errors[] = "Error creating user.";
                    }
                }
            }
        }

        $users = User::all(1000); // List all for admin

        $view = new View();
        $view->render('admin/users', [
            'currentUser' => $currentUser,
            'errors' => $errors,
            'success' => $success,
            'users' => $users
        ], 'Admin - Users');
    }

    public function languages() {
        Auth::requireLogin();
        Auth::requireAdmin();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add'])) {
                $name = trim($_POST['name'] ?? '');
                if ($name === '') {
                    $errors[] = "Language name is required.";
                } else {
                    $slug = Helper::safeSlug($name);
                    if (Language::findByName($name)) {
                        $errors[] = "Language already exists.";
                    } else {
                        Language::create($name, $slug);
                    }
                }
            } elseif (isset($_POST['delete_id'])) {
                $id = (int)$_POST['delete_id'];
                if (Language::isUsed($id)) {
                    $errors[] = "Cannot delete language that is used by lab programs.";
                } else {
                    Language::delete($id);
                }
            }
        }

        $langs = Language::all();

        $view = new View();
        $view->render('admin/languages', [
            'errors' => $errors,
            'langs' => $langs
        ], 'Admin - Languages');
    }

    public function subjects() {
        Auth::requireLogin();
        Auth::requireAdmin();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add'])) {
                $name = trim($_POST['name'] ?? '');
                if ($name === '') {
                    $errors[] = "Subject name is required.";
                } else {
                    $slug = Helper::safeSlug($name);
                    if (Subject::findByName($name)) {
                        $errors[] = "Subject already exists.";
                    } else {
                        Subject::create($name, $slug);
                    }
                }
            } elseif (isset($_POST['delete_id'])) {
                $id = (int)$_POST['delete_id'];
                if (Subject::isUsed($id)) {
                    $errors[] = "Cannot delete subject that is used by homework.";
                } else {
                    Subject::delete($id);
                }
            }
        }

        $subjects = Subject::all();

        $view = new View();
        $view->render('admin/subjects', [
            'errors' => $errors,
            'subjects' => $subjects
        ], 'Admin - Subjects');
    }
}
