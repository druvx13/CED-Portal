<section class="page auth-page">
    <h1>Login</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= \App\Core\View::h($error) ?></div>
    <?php endif; ?>
    <form method="post" class="form">
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Login</button>
    </form>
</section>
