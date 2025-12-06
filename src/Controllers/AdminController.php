<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Utils\Helper;
use App\Models\User;
use App\Models\Language;
use App\Models\Subject;

class AdminController {
    public function users() {
        Auth::requireLogin();
        Auth::requireAdmin();
        $user = Auth::user();

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::requireSuperAdmin(); // Only first admin can create users

            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $is_admin = !empty($_POST['is_admin']) ? 1 : 0;

            if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters.";
            if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";

            if (!$errors) {
                if (User::findByUsername($username)) {
                    $errors[] = "Username already taken.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $newId = User::create($username, $hash, $is_admin, $user['id']);
                        $success = "User created (ID: {$newId}).";
                    } catch (\Throwable $e) {
                        $errors[] = "Error creating user.";
                    }
                }
            }
        }

        $users = User::all(1000, 0); // Fetch all for admin view

        View::render('admin/users', [
            'users' => $users,
            'errors' => $errors,
            'success' => $success,
            'user' => $user,
            'title' => 'Admin - Users'
        ]);
    }

    public function languages() {
        Auth::requireLogin();
        Auth::requireAdmin();
        $user = Auth::user();

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
                if (Language::countPrograms($id) > 0) {
                    $errors[] = "Cannot delete language that is used by lab programs.";
                } else {
                    Language::delete($id);
                }
            }
        }

        $langs = Language::all();

        View::render('admin/languages', [
            'langs' => $langs,
            'errors' => $errors,
            'user' => $user,
            'title' => 'Admin - Languages'
        ]);
    }

    public function subjects() {
        Auth::requireLogin();
        Auth::requireAdmin();
        $user = Auth::user();

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
                if (Subject::countHomework($id) > 0) {
                    $errors[] = "Cannot delete subject that is used by homework.";
                } else {
                    Subject::delete($id);
                }
            }
        }

        $subjects = Subject::all();

        View::render('admin/subjects', [
            'subjects' => $subjects,
            'errors' => $errors,
            'user' => $user,
            'title' => 'Admin - Subjects'
        ]);
    }
}
