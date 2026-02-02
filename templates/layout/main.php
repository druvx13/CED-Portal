<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= \App\Utils\Helper::h($title ?? 'CED Portal') ?> - CED Portal</title>
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

        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav" id="navMenu">
            <a href="<?= BASE_URL ?>/">Dashboard</a>
            <a href="<?= BASE_URL ?>/lab-programs">Lab Programs</a>
            <a href="<?= BASE_URL ?>/manuals">Lab Manuals</a>
            <a href="<?= BASE_URL ?>/homework">Homework</a>
            <a href="<?= BASE_URL ?>/users">Users</a>
            <?php if ($user ?? null): ?>
                <a href="<?= BASE_URL ?>/reminders">Reminders</a>
                <a href="<?= BASE_URL ?>/notes">Notes</a>
                <?php if ($user['is_admin']): ?>
                    <a href="<?= BASE_URL ?>/admin/users">Admin</a>
                    <a href="<?= BASE_URL ?>/admin/languages">Languages</a>
                    <a href="<?= BASE_URL ?>/admin/subjects">Subjects</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="auth">
            <?php if ($user ?? null): ?>
                <span class="auth__user">
                    <?= \App\Utils\Helper::h($user['username']) ?><?= $user['is_admin'] ? ' (admin)' : '' ?>
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
    <?= $content ?>
</main>
<footer class="site-footer">
    <p>
  <strong>Disclaimer:</strong> I, <strong style="color: #FF0000;">Nikol</strong>, am a Diploma student in Computer Engineering. The content I share originates from materials accessible through my academic program and represents my coursework, projects, or personal study—shared strictly for educational purposes.
	</p>

	<center><p>
  <img src="https://nikol.22web.org/img/members/2/mini_cooltext497354994916342.png" alt="Nikol Logo">
	</p>

	<p>
  All content herein has been uploaded by <strong style="color: #FF0000;">NIKOL</strong>.
	</p></center>
</footer>
<script src="<?= BASE_URL ?>/assets/app.js"></script>
</body>
</html>
