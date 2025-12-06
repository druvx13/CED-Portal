<section class="page">
    <h1>Posts by <?= \App\Core\View::h($user['username']) ?> <?= $user['is_admin'] ? '(Admin)' : '' ?></h1>

    <?php $base = BASE_URL . '/users/posts?id=' . $user['id']; ?>

    <section class="user-post-section">
        <h2>Lab Programs</h2>
        <?php if ($userPrograms): ?>
            <ul>
                <?php foreach ($userPrograms as $p): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                            <?= \App\Core\View::h($p['title']) ?>
                        </a>
                        <span class="muted">
                            (<?= \App\Core\View::h($p['language_name']) ?>,
                            <?= \App\Core\View::h($p['created_at']) ?>)
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($totPagesProg > 1): ?>
                <nav class="pagination">
                    <?php if ($pageProg > 1): ?>
                        <a href="<?= $base ?>&page_prog=<?= $pageProg - 1 ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $pageProg ?> of <?= $totPagesProg ?></span>
                    <?php if ($pageProg < $totPagesProg): ?>
                        <a href="<?= $base ?>&page_prog=<?= $pageProg + 1 ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">No lab programs posted.</p>
        <?php endif; ?>
    </section>

    <section class="user-post-section">
        <h2>Lab Manuals</h2>
        <?php if ($userManuals): ?>
            <ul>
                <?php foreach ($userManuals as $m): ?>
                    <li>
                        <a href="<?= \App\Core\View::h($m['pdf_path']) ?>" target="_blank">
                            <?= \App\Core\View::h($m['title']) ?>
                        </a>
                        <span class="muted">(<?= \App\Core\View::h($m['created_at']) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($totPagesMan > 1): ?>
                <nav class="pagination">
                    <?php if ($pageMan > 1): ?>
                        <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan - 1 ?>&page_hw=<?= $pageHw ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $pageMan ?> of <?= $totPagesMan ?></span>
                    <?php if ($pageMan < $totPagesMan): ?>
                        <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan + 1 ?>&page_hw=<?= $pageHw ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">No lab manuals posted.</p>
        <?php endif; ?>
    </section>

    <section class="user-post-section">
        <h2>Homework</h2>
        <?php if ($userHomework): ?>
            <ul>
                <?php foreach ($userHomework as $h): ?>
                    <li>
                        <?= \App\Core\View::h($h['title']) ?>
                        <span class="muted">
                            (subject <?= \App\Core\View::h($h['subject_name']) ?>,
                            due <?= \App\Core\View::h($h['due_date'] ?? '—') ?>,
                            created <?= \App\Core\View::h($h['created_at']) ?>)
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($totPagesHw > 1): ?>
                <nav class="pagination">
                    <?php if ($pageHw > 1): ?>
                        <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw - 1 ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $pageHw ?> of <?= $totPagesHw ?></span>
                    <?php if ($pageHw < $totPagesHw): ?>
                        <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">No homework posted.</p>
        <?php endif; ?>
    </section>
</section>
