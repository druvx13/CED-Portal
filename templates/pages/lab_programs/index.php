<section class="page">
    <h1>Lab Programs</h1>
    <?php if ($user): ?>
        <p><a class="btn btn--primary" href="<?= BASE_URL ?>/lab-programs/new">Upload new program</a></p>
    <?php else: ?>
        <p class="muted">Login to upload new programs. Existing ones are visible to everyone.</p>
    <?php endif; ?>

    <?php if (!$grouped): ?>
        <p class="muted">No lab programs uploaded yet.</p>
    <?php else: ?>
        <nav class="subject-nav" aria-label="Jump to subject">
            <?php foreach ($grouped as $language => $programs): ?>
                <a class="subject-nav__item" href="#subject-<?= htmlspecialchars(preg_replace('/\W+/', '-', strtolower($language)), ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Utils\Helper::h($language) ?>
                    <span class="subject-nav__count"><?= count($programs) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ($grouped as $language => $programs): ?>
            <?php $anchor = preg_replace('/\W+/', '-', strtolower($language)); ?>
            <section class="card subject-section" id="subject-<?= htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8') ?>">
                <div class="subject-section__header" role="button" tabindex="0" aria-expanded="true" aria-controls="subject-body-<?= htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8') ?>">
                    <h2><?= \App\Utils\Helper::h($language) ?></h2>
                    <span class="subject-section__meta">
                        <span class="badge"><?= count($programs) ?> program<?= count($programs) !== 1 ? 's' : '' ?></span>
                        <span class="subject-section__toggle" aria-hidden="true">&#9650;</span>
                    </span>
                </div>
                <ul id="subject-body-<?= htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8') ?>">
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
    <?php endif; ?>
</section>
