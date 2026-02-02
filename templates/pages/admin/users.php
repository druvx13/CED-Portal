<section class="page">
    <h1>User Management</h1>

    <?php if (!empty($errors)): ?>
        <div class="c-alert c-alert--error" role="alert">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="notice"><?= \App\Utils\Helper::h($success) ?></p>
    <?php endif; ?>

    <?php if ((int)$user['id'] === 1 && $user['is_first_admin']): ?>
        <h2>Create user</h2>
        <form method="post" class="c-form">
            <?= \App\Utils\Helper::csrfField() ?>
            <div class="c-form__group">
                <label for="username" class="c-form__label">Username</label>
                <input type="text" id="username" name="username" class="c-form__input" required aria-required="true" autocomplete="username">
            </div>
            <div class="c-form__group">
                <label for="password" class="c-form__label">Password</label>
                <input type="password" id="password" name="password" class="c-form__input" required aria-required="true" autocomplete="new-password">
            </div>
            <div class="c-form__group">
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_admin" value="1">
                    Make admin
                </label>
            </div>
            <div class="c-form__actions">
                <button type="submit" class="c-btn c-btn--primary">Create user</button>
            </div>
        </form>
    <?php else: ?>
        <p class="u-text-muted">Only the first admin (ID=1) can create users.</p>
    <?php endif; ?>

    <h2>Existing users</h2>
    <table class="c-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Admin</th>
                <th>First Admin</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= \App\Utils\Helper::h($u['username']) ?></td>
                <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
                <td><?= $u['is_first_admin'] ? 'Yes' : 'No' ?></td>
                <td><?= \App\Utils\Helper::h($u['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
