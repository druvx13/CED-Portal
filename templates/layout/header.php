<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= \App\Core\View::h($title) ?> - CED Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <!-- Highlight.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>document.addEventListener('DOMContentLoaded', () => { if (window.hljs) { hljs.highlightAll(); } });</script>
</head>
<body>
<header class="site-header">
    <div class="site-header__inner">
        <a href="<?= BASE_URL ?>/" class="logo">CED Portal</a>

        <nav class="nav">
            <a href="<?= BASE_URL ?>/">Dashboard</a>
            <a href="<?= BASE_URL ?>/lab-programs">Lab Programs</a>
            <a href="<?= BASE_URL ?>/manuals">Lab Manuals</a>
            <a href="<?= BASE_URL ?>/homework">Homework</a>
            <a href="<?= BASE_URL ?>/users">Users</a>
            <?php $currentUser = \App\Core\Auth::user(); ?>
            <?php if ($currentUser): ?>
                <a href="<?= BASE_URL ?>/reminders">Reminders</a>
                <a href="<?= BASE_URL ?>/notes">Notes</a>
                <?php if ($currentUser['is_admin']): ?>
                    <a href="<?= BASE_URL ?>/admin/users">Admin</a>
                    <a href="<?= BASE_URL ?>/admin/languages">Languages</a>
                    <a href="<?= BASE_URL ?>/admin/subjects">Subjects</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="auth">
            <?php if ($currentUser): ?>
                <span class="auth__user">
                    <?= \App\Core\View::h($currentUser['username']) ?><?= $currentUser['is_admin'] ? ' (admin)' : '' ?>
                </span>
                <form method="post" action="<?= BASE_URL ?>/logout" class="auth__form">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
