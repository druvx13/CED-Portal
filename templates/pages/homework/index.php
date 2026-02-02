<section class="page">
    <h1>Homework</h1>
    <?php if ($user && $user['is_admin']): ?>
        <p><a class="c-btn c-btn--small" href="<?= BASE_URL ?>/homework/new">Create homework</a></p>
    <?php else: ?>
        <p class="u-text-muted">Homework questions are visible to everyone. Only staff can post new ones.</p>
    <?php endif; ?>

    <?php if (!$grouped): ?>
        <p class="u-text-muted">No homework assigned yet.</p>
    <?php else: ?>
        <?php foreach ($grouped as $subject => $items): ?>
            <section class="c-card">
                <header class="c-card__header">
                    <h2 class="c-card__title"><?= \App\Utils\Helper::h($subject) ?></h2>
                </header>
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Due date</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <?php if ($user): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $hw): ?>
                        <tr>
                            <td><?= \App\Utils\Helper::h($hw['title']) ?></td>
                            <td><?= \App\Utils\Helper::h($hw['due_date'] ?? '—') ?></td>
                            <td><?= nl2br(\App\Utils\Helper::h(mb_strimwidth($hw['question'], 0, 160, '…', 'UTF-8'))) ?></td>
                            <td>
                                <?php if ($hw['answer_path'] && (!$hw['due_date'] || $hw['due_date'] <= date('Y-m-d H:i:s'))): ?>
                                    <a href="<?= \App\Utils\Helper::h($hw['answer_path']) ?>" target="_blank">Download</a>
                                <?php elseif ($hw['answer_path']): ?>
                                    <span class="u-text-muted">Locked until due date</span>
                                <?php else: ?>
                                    <span class="u-text-muted">Not available</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($user): ?>
                                <td>
                                    <?php if ($user['is_admin'] || (int)$user['id'] === (int)$hw['uploaded_by']): ?>
                                        <a href="<?= BASE_URL ?>/homework/edit?id=<?= (int)$hw['id'] ?>">Edit</a>
                                        <form method="post" action="<?= BASE_URL ?>/homework/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this homework?');">
                                            <?= \App\Utils\Helper::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int)$hw['id'] ?>">
                                            <button type="submit" class="c-btn">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="u-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?= BASE_URL ?>/homework?page=<?= $page - 1 ?>">&laquo; Prev</a>
                <?php endif; ?>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= BASE_URL ?>/homework?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
