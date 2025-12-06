<?php
namespace Controllers;

use Core\Controller;
use Config\Database;

class DashboardController extends Controller {
    public function index() {
        $pdo = Database::connect();
        $currentUser = null;
        $reminders = [];

        if (!empty($_SESSION['user_id'])) {
             // For logged-in user: reminders
             $stmt = $pdo->prepare("
                SELECT id, message, due_date
                FROM reminders
                WHERE user_id = ? AND due_date >= NOW()
                ORDER BY due_date ASC
                LIMIT 3
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $reminders = $stmt->fetchAll();
        }

        // Recent lab programs
        $stmt = $pdo->query("
            SELECT
                lp.id,
                lp.title,
                lp.code,
                lp.created_at,
                lp.uploaded_by,
                u.username,
                COALESCE(pl.name, lp.language) AS language_name,
                COALESCE(pl.slug, lp.language) AS language_slug
            FROM lab_programs lp
            LEFT JOIN programming_languages pl ON lp.language_id = pl.id
            LEFT JOIN users u ON lp.uploaded_by = u.id
            ORDER BY lp.created_at DESC
            LIMIT 5
        ");
        $programs = $stmt->fetchAll();

        // Recent manuals
        $stmt = $pdo->query("
            SELECT id, title, pdf_path, created_at
            FROM lab_manuals
            ORDER BY created_at DESC
            LIMIT 3
        ");
        $manuals = $stmt->fetchAll();

        $this->view('layout/header', ['title' => 'Dashboard']);
        $this->view('dashboard/index', [
            'reminders' => $reminders,
            'programs' => $programs,
            'manuals' => $manuals
        ]);
        $this->view('layout/footer');
    }
}
