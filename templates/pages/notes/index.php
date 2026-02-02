<section class="page">
    <h1>Notes</h1>

    <?php if (!empty($errors)): ?>
        <div class="c-alert c-alert--error" role="alert">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="c-form">
        <?= \App\Utils\Helper::csrfField() ?>
        <div class="c-form__group">
            <label for="title" class="c-form__label">Title</label>
            <input type="text" id="title" name="title" class="c-form__input" required aria-required="true" autocomplete="off" value="<?= \App\Utils\Helper::h($title_val) ?>">
        </div>
        <div class="c-form__group">
            <label for="body" class="c-form__label">Body</label>
            <textarea id="body" name="body" rows="6" class="c-form__textarea" required aria-required="true"><?= \App\Utils\Helper::h($body_val) ?></textarea>
        </div>
        <div class="c-form__actions">
            <button type="submit" class="c-btn c-btn--primary">Add note</button>
        </div>
    </form>

    <h2>Your notes</h2>
    <?php if ($notes): ?>
        <ul class="notes-list">
            <?php foreach ($notes as $n): ?>
                <li>
                    <h3><?= \App\Utils\Helper::h($n['title']) ?></h3>
                    <div class="u-text-muted"><?= \App\Utils\Helper::h($n['created_at']) ?></div>
                    <p><?= nl2br(\App\Utils\Helper::h($n['body'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="u-text-muted">No notes yet.</p>
    <?php endif; ?>
</section>
