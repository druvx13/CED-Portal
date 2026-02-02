<section class="page dashboard">
    <h1>Dashboard</h1>
    <p class="u-text-muted">
        Guests can browse content. Log in to add your own lab programs, manuals, homework and reminders.
    </p>

    <div class="dashboard-grid">
        <?php if ($user): ?>
            <section class="c-card card--reminders">
                <header class="c-card__header">
                    <h2 class="c-card__title">Upcoming Reminders</h2>
                    <a href="<?= BASE_URL ?>/reminders" class="card__link">View all</a>
                </header>
                <?php if ($reminders): ?>
                    <ul class="reminder-list">
                        <?php foreach ($reminders as $r): ?>
                            <li>
                                <div class="reminder-msg"><?= \App\Utils\Helper::h($r['message']) ?></div>
                                <div class="reminder-date"><?= \App\Utils\Helper::h($r['due_date']) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="u-text-muted">No upcoming reminders.</p>
                <?php endif; ?>
                <a class="c-btn c-btn--small" href="<?= BASE_URL ?>/reminders">Add reminder</a>
            </section>
        <?php endif; ?>

        <section class="c-card card--programs">
            <header class="c-card__header">
                <h2 class="c-card__title">Recent Lab Programs</h2>
                <a href="<?= BASE_URL ?>/lab-programs" class="card__link">All programs</a>
            </header>
            <?php if ($programs): ?>
                <div class="code-snippet-list">
                    <?php foreach ($programs as $p): ?>
                        <article class="code-snippet">
                            <header>
                                <h3>
                                    <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                                        <?= \App\Utils\Helper::h($p['title']) ?>
                                    </a>
                                </h3>
                                <span class="badge"><?= \App\Utils\Helper::h($p['language_name']) ?></span>
                            </header>
                            <pre><code class="hljs language-<?= \App\Utils\Helper::h($p['language_slug']) ?>"><?php
                                $preview = mb_substr($p['code'], 0, 260, 'UTF-8');
                                echo \App\Utils\Helper::h($preview . (mb_strlen($p['code'], 'UTF-8') > 260 ? "...\n" : ""));
                            ?></code></pre>
                            <div class="code-snippet-meta">
                                <span class="u-text-muted">By <?= \App\Utils\Helper::h($p['username'] ?? 'Unknown') ?></span>
                                <span class="u-text-muted"><?= \App\Utils\Helper::h($p['created_at']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="u-text-muted">No programs yet.</p>
            <?php endif; ?>
            <?php if ($user): ?>
                <a class="c-btn c-btn--small" href="<?= BASE_URL ?>/lab-programs/new">Upload program</a>
            <?php endif; ?>
        </section>

        <section class="c-card card--manuals">
            <header class="c-card__header">
                <h2 class="c-card__title">Latest Lab Manuals</h2>
                <a href="<?= BASE_URL ?>/manuals" class="card__link">All manuals</a>
            </header>
            <?php if ($manuals): ?>
                <ul class="manual-list">
                    <?php foreach ($manuals as $m): ?>
                        <li>
                            <a href="<?= \App\Utils\Helper::h($m['pdf_path']) ?>" target="_blank">
                                <?= \App\Utils\Helper::h($m['title']) ?>
                            </a>
                            <span class="u-text-muted"><?= \App\Utils\Helper::h($m['created_at']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="u-text-muted">No manuals yet.</p>
            <?php endif; ?>
            <?php if ($user): ?>
                <a class="c-btn c-btn--small" href="<?= BASE_URL ?>/manuals/new">Upload manual</a>
            <?php endif; ?>
        </section>
    </div>
</section>
