<section class="page">
    <h1>Lab Manuals</h1>
    <?php if ($user): ?>
        <p><a class="btn btn--primary" href="<?= BASE_URL ?>/manuals/new">Upload manual</a></p>
    <?php else: ?>
        <p class="muted">Login to upload manuals. Everyone can download them.</p>
    <?php endif; ?>

    <?php if ($manuals): ?>
        <ul class="manual-list">
            <?php foreach ($manuals as $m): ?>
                <li>
                    <a href="<?= \App\Utils\Helper::h($m['pdf_path']) ?>" target="_blank">
                        <?= \App\Utils\Helper::h($m['title']) ?>
                    </a>
                    <span class="muted">
                        (<?= \App\Utils\Helper::h($m['created_at']) ?>,
                        by <?= \App\Utils\Helper::h($m['username'] ?? 'Unknown') ?>)
                    </span>
                    <?php if ($user && ($user['is_admin'] || (int)$user['id'] === (int)$m['uploaded_by'])): ?>
                        <span>
                            |
                            <a href="<?= BASE_URL ?>/manuals/edit?id=<?= (int)$m['id'] ?>">Edit</a>
                            <form method="post" action="<?= BASE_URL ?>/manuals/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this manual?');">
                                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= BASE_URL ?>/manuals?page=<?= $page - 1 ?>">&laquo; Prev</a>
                <?php endif; ?>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= BASE_URL ?>/manuals?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <p class="muted">No manuals uploaded yet.</p>
    <?php endif; ?>
</section>
