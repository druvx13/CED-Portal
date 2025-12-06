<section class="page">
    <h1>Lab Programs</h1>
    <?php if ($user): ?>
        <p><a class="btn btn--small" href="<?= BASE_URL ?>/lab-programs/new">Upload new program</a></p>
    <?php else: ?>
        <p class="muted">Login to upload new programs. Existing ones are visible to everyone.</p>
    <?php endif; ?>

    <?php if (!$grouped): ?>
        <p class="muted">No lab programs uploaded yet.</p>
    <?php else: ?>
        <?php foreach ($grouped as $language => $programs): ?>
            <section class="card">
                <h2><?= \App\Utils\Helper::h($language) ?></h2>
                <ul>
                    <?php foreach ($programs as $p): ?>
                        <li>
                            <strong>
                                <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                                    <?= \App\Utils\Helper::h($p['title']) ?>
                                </a>
                            </strong>
                            <span class="muted">
                                by <?= \App\Utils\Helper::h($p['username'] ?? 'Unknown') ?> · <?= \App\Utils\Helper::h($p['created_at']) ?>
                            </span>
                            <?php if ($user && ($user['is_admin'] || (int)$user['id'] === (int)$p['uploaded_by'])): ?>
                                <span>
                                    |
                                    <a href="<?= BASE_URL ?>/lab-programs/edit?id=<?= (int)$p['id'] ?>">Edit</a>
                                    <form method="post" action="<?= BASE_URL ?>/lab-programs/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this program?');">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </span>
                            <?php endif; ?>
                            <details>
                                <summary>Code preview</summary>
                                <pre><code class="hljs language-<?= \App\Utils\Helper::h($p['language_slug']) ?>"><?= \App\Utils\Helper::h(mb_strimwidth($p['code'], 0, 500, "\n…", 'UTF-8')) ?></code></pre>
                            </details>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= BASE_URL ?>/lab-programs?page=<?= $page - 1 ?>">&laquo; Prev</a>
                <?php endif; ?>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= BASE_URL ?>/lab-programs?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
