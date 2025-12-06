<section class="page">
    <h1>Users</h1>

    <?php if ($users): ?>
        <table class="changes-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Posts</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= \App\Core\View::h($u['username']) ?></td>
                        <td><?= $u['is_admin'] ? 'Admin' : 'User' ?></td>
                        <td><?= \App\Core\View::h($u['created_at']) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/users/posts?id=<?= (int)$u['id'] ?>">View posts</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= BASE_URL ?>/users?page=<?= $page - 1 ?>">&laquo; Prev</a>
                <?php endif; ?>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= BASE_URL ?>/users?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <p class="muted">No users yet.</p>
    <?php endif; ?>
</section>
