<section class="page">
    <p class="subject-breadcrumb">
        <a href="<?= BASE_URL ?>/lab-programs">&larr; All Subjects</a>
    </p>
    <h1><?= \App\Utils\Helper::h($languageName) ?> Programs</h1>
    <p class="muted"><?= count($programs) ?> program<?= count($programs) !== 1 ? 's' : '' ?></p>

    <?php if ($user): ?>
        <p><a class="btn btn--primary" href="<?= BASE_URL ?>/lab-programs/new">Upload new program</a></p>
    <?php endif; ?>

    <div class="subject-program-list">
        <?php foreach ($programs as $p): ?>
            <div class="subject-program-item card">
                <div class="subject-program-item__header">
                    <h2 class="subject-program-item__title">
                        <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                            <?= \App\Utils\Helper::h($p['title']) ?>
                        </a>
                    </h2>
                    <div class="subject-program-item__actions">
                        <a class="btn btn--small" href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">View</a>
                        <?php if ($user && ($user['is_admin'] || (int)$user['id'] === (int)$p['uploaded_by'])): ?>
                            <a class="btn btn--small" href="<?= BASE_URL ?>/lab-programs/edit?id=<?= (int)$p['id'] ?>">Edit</a>
                            <form method="post" action="<?= BASE_URL ?>/lab-programs/delete" class="inline-form" onsubmit="return confirm('Delete this program?');">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn btn--small btn--danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="muted subject-program-item__meta">
                    by <?= \App\Utils\Helper::h($p['username'] ?? 'Unknown') ?> &middot; <?= \App\Utils\Helper::h($p['created_at']) ?>
                </p>
                <details>
                    <summary>Code preview</summary>
                    <pre><code class="hljs language-<?= \App\Utils\Helper::h($p['language_slug']) ?>"><?= \App\Utils\Helper::h(mb_strimwidth($p['code'], 0, 500, "\n…", 'UTF-8')) ?></code></pre>
                </details>
            </div>
        <?php endforeach; ?>
    </div>
</section>
