<section class="page">
    <h1>Edit Lab Manual</h1>

    <?php if (!empty($errors)): ?>
        <div class="c-alert c-alert--error" role="alert">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="c-form" enctype="multipart/form-data">
        <?= \App\Utils\Helper::csrfField() ?>
        <input type="hidden" name="id" value="<?= (int)$manual['id'] ?>">
        <div class="c-form__group">
            <label for="title" class="c-form__label">Title</label>
            <input type="text" id="title" name="title" class="c-form__input" required aria-required="true" autocomplete="off" value="<?= \App\Utils\Helper::h($title_val) ?>">
        </div>
        <div class="c-form__group">
            <label for="manual_file" class="c-form__label">Replace PDF (optional)</label>
            <input type="file" id="manual_file" name="manual_file" class="c-form__input" accept="application/pdf">
        </div>
        <div class="c-form__actions">
            <button type="submit" class="c-btn c-btn--primary">Save changes</button>
        </div>
    </form>
</section>
