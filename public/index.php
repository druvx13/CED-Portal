<?php

require_once __DIR__ . '/../src/Autoloader.php';

use App\Config\Config;
use App\Core\Bootstrap;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LabProgramController;
use App\Controllers\ManualController;
use App\Controllers\HomeworkController;
use App\Controllers\ReminderController;
use App\Controllers\NoteController;
use App\Controllers\UserController;
use App\Controllers\AdminController;

session_start();

// Load environment variables
Config::load(__DIR__ . '/../.env');

// Bootstrap Application
Bootstrap::run();

// Router Setup
$router = new Router();

// Auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// Lab Programs
$router->get('/lab-programs', [LabProgramController::class, 'index']);
$router->get('/lab-programs/new', [LabProgramController::class, 'create']);
$router->post('/lab-programs/new', [LabProgramController::class, 'create']);
$router->get('/lab-programs/view', [LabProgramController::class, 'view']);
$router->get('/lab-programs/edit', [LabProgramController::class, 'edit']);
$router->post('/lab-programs/edit', [LabProgramController::class, 'edit']);
$router->post('/lab-programs/delete', [LabProgramController::class, 'delete']);

// Manuals
$router->get('/manuals', [ManualController::class, 'index']);
$router->get('/manuals/new', [ManualController::class, 'create']);
$router->post('/manuals/new', [ManualController::class, 'create']);
$router->get('/manuals/edit', [ManualController::class, 'edit']);
$router->post('/manuals/edit', [ManualController::class, 'edit']);
$router->post('/manuals/delete', [ManualController::class, 'delete']);

// Homework
$router->get('/homework', [HomeworkController::class, 'index']);
$router->get('/homework/new', [HomeworkController::class, 'create']);
$router->post('/homework/new', [HomeworkController::class, 'create']);
$router->get('/homework/edit', [HomeworkController::class, 'edit']);
$router->post('/homework/edit', [HomeworkController::class, 'edit']);
$router->post('/homework/delete', [HomeworkController::class, 'delete']);

// Reminders
$router->get('/reminders', [ReminderController::class, 'index']);
$router->post('/reminders', [ReminderController::class, 'index']);

// Notes
$router->get('/notes', [NoteController::class, 'index']);
$router->post('/notes', [NoteController::class, 'index']);

// Users
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/posts', [UserController::class, 'posts']);

// Admin
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/languages', [AdminController::class, 'languages']);
$router->post('/admin/languages', [AdminController::class, 'languages']);
$router->get('/admin/subjects', [AdminController::class, 'subjects']);
$router->post('/admin/subjects', [AdminController::class, 'subjects']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
