<section class="page">
    <h1>User Management</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Core\View::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="notice"><?= \App\Core\View::h($success) ?></p>
    <?php endif; ?>

    <?php if ((int)$currentUser['id'] === 1 && $currentUser['is_first_admin']): ?>
        <h2>Create user</h2>
        <form method="post" class="form">
            <label>
                Username
                <input type="text" name="username" required>
            </label>
            <label>
                Password
                <input type="password" name="password" required>
            </label>
            <label class="checkbox-inline">
                <input type="checkbox" name="is_admin" value="1">
                Make admin
            </label>
            <button type="submit">Create user</button>
        </form>
    <?php else: ?>
        <p class="muted">Only the first admin (ID=1) can create users.</p>
    <?php endif; ?>

    <h2>Existing users</h2>
    <table class="changes-table">
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
                <td><?= \App\Core\View::h($u['username']) ?></td>
                <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
                <td><?= $u['is_first_admin'] ? 'Yes' : 'No' ?></td>
                <td><?= \App\Core\View::h($u['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
