<section class="page auth-page">
    <h1>Login</h1>
    <?php if (!empty($error)): ?>
        <div class="c-alert c-alert--error" role="alert"><?= \App\Utils\Helper::h($error) ?></div>
    <?php endif; ?>
    <form method="post" class="c-form">
        <?= \App\Utils\Helper::csrfField() ?>
        <div class="c-form__group">
            <label for="username" class="c-form__label c-form__label--required">Username</label>
            <input type="text" id="username" name="username" class="c-form__input" required aria-required="true" autocomplete="username">
        </div>
        <div class="c-form__group">
            <label for="password" class="c-form__label c-form__label--required">Password</label>
            <input type="password" id="password" name="password" class="c-form__input" required aria-required="true" autocomplete="current-password">
        </div>
        <div class="c-form__actions">
            <button type="submit" class="c-btn c-btn--primary">Login</button>
        </div>
    </form>
</section>
