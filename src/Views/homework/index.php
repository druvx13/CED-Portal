<section class="page">
    <h1>Homework</h1>
    <?php if (!empty($_SESSION['is_admin'])): ?>
        <p><a class="btn btn--small" href="<?= BASE_URL ?>/homework/new">Create homework</a></p>
    <?php else: ?>
        <p class="muted">Homework questions are visible to everyone. Only staff can post new ones.</p>
    <?php endif; ?>

    <?php if (!$grouped): ?>
        <p class="muted">No homework assigned yet.</p>
    <?php else: ?>
        <?php foreach ($grouped as $subject => $items): ?>
            <section class="card">
                <h2><?= h($subject) ?></h2>
                <table class="changes-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Due date</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $hw): ?>
                        <tr>
                            <td><?= h($hw['title']) ?></td>
                            <td><?= h($hw['due_date'] ?? '—') ?></td>
                            <td><?= nl2br(h(mb_strimwidth($hw['question'], 0, 160, '…', 'UTF-8'))) ?></td>
                            <td>
                                <?php if ($hw['answer_path'] && (!$hw['due_date'] || $hw['due_date'] <= date('Y-m-d H:i:s'))): ?>
                                    <a href="<?= h($hw['answer_path']) ?>" target="_blank">Download</a>
                                <?php elseif ($hw['answer_path']): ?>
                                    <span class="muted">Locked until due date</span>
                                <?php else: ?>
                                    <span class="muted">Not available</span>
                                <?php endif; ?>
                            </td>
                            <?php if (!empty($_SESSION['user_id'])): ?>
                                <td>
                                    <?php if (!empty($_SESSION['is_admin']) || (int)$_SESSION['user_id'] === (int)$hw['uploaded_by']): ?>
                                        <a href="<?= BASE_URL ?>/homework/edit?id=<?= (int)$hw['id'] ?>">Edit</a>
                                        <form method="post" action="<?= BASE_URL ?>/homework/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this homework?');">
                                            <input type="hidden" name="id" value="<?= (int)$hw['id'] ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted">—</span>
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
