<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= \App\Utils\Helper::h($title ?? 'CED Portal') ?> - CED Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CED Portal - Computer Engineering educational platform for lab programs, manuals, and homework management">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style-new.css">
    <!-- Highlight.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>document.addEventListener('DOMContentLoaded', () => { if (window.hljs) { hljs.highlightAll(); } });</script>
</head>
<body>
<header class="c-header" role="banner">
    <div class="c-header__inner">
        <a href="<?= BASE_URL ?>/" class="c-header__logo">CED Portal</a>

        <nav class="c-nav" role="navigation" aria-label="Main navigation">
            <a href="<?= BASE_URL ?>/" class="c-nav__link">Dashboard</a>
            <a href="<?= BASE_URL ?>/lab-programs" class="c-nav__link">Lab Programs</a>
            <a href="<?= BASE_URL ?>/manuals" class="c-nav__link">Lab Manuals</a>
            <a href="<?= BASE_URL ?>/homework" class="c-nav__link">Homework</a>
            <a href="<?= BASE_URL ?>/users" class="c-nav__link">Users</a>
            <?php if ($user ?? null): ?>
                <a href="<?= BASE_URL ?>/reminders" class="c-nav__link">Reminders</a>
                <a href="<?= BASE_URL ?>/notes" class="c-nav__link">Notes</a>
                <?php if ($user['is_admin']): ?>
                    <a href="<?= BASE_URL ?>/admin/users" class="c-nav__link">Admin</a>
                    <a href="<?= BASE_URL ?>/admin/languages" class="c-nav__link">Languages</a>
                    <a href="<?= BASE_URL ?>/admin/subjects" class="c-nav__link">Subjects</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="c-auth">
            <?php if ($user ?? null): ?>
                <span class="c-auth__user">
                    <?= \App\Utils\Helper::h($user['username']) ?><?= $user['is_admin'] ? ' (admin)' : '' ?>
                </span>
                <form method="post" action="<?= BASE_URL ?>/logout" class="c-auth__form">
                    <?= \App\Utils\Helper::csrfField() ?>
                    <button type="submit" class="c-btn c-btn--small">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="c-btn c-btn--small">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="c-main" role="main">
    <?= $content ?>
</main>
<footer class="c-footer" role="contentinfo">
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
