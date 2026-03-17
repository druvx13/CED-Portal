<section class="page">
    <h1>Lab Programs</h1>
    <?php if ($user): ?>
        <p><a class="btn btn--primary" href="<?= BASE_URL ?>/lab-programs/new">Upload new program</a></p>
    <?php else: ?>
        <p class="muted">Login to upload new programs. Existing ones are visible to everyone.</p>
    <?php endif; ?>

    <?php if (!$languages): ?>
        <p class="muted">No lab programs uploaded yet.</p>
    <?php else: ?>
        <div class="subject-grid">
            <?php foreach ($languages as $lang): ?>
                <a class="subject-card" href="<?= BASE_URL ?>/lab-programs/subject?slug=<?= urlencode($lang['language_slug']) ?>">
                    <span class="subject-card__name"><?= \App\Utils\Helper::h($lang['language_name']) ?></span>
                    <span class="subject-card__count"><?= (int)$lang['program_count'] ?> program<?= (int)$lang['program_count'] !== 1 ? 's' : '' ?></span>
                    <span class="subject-card__arrow">Browse &rsaquo;</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
