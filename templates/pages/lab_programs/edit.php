<section class="page">
    <h1>Edit Lab Program</h1>

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
        <input type="hidden" name="id" value="<?= (int)$program['id'] ?>">
        <div class="c-form__group">
            <label for="title" class="c-form__label">Title</label>
            <input type="text" id="title" name="title" class="c-form__input" required aria-required="true" autocomplete="off" value="<?= \App\Utils\Helper::h($title_val) ?>">
        </div>
        <div class="c-form__group">
            <label for="language_id" class="c-form__label">Language</label>
            <select id="language_id" name="language_id" class="c-form__select" required aria-required="true">
                <option value="">Select language</option>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= (int)$lang['id'] ?>" <?= $language_id_val === (int)$lang['id'] ? 'selected' : '' ?>>
                        <?= \App\Utils\Helper::h($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="c-form__group">
            <label for="code" class="c-form__label">Code</label>
            <textarea id="code" name="code" rows="15" class="c-form__textarea textarea--mono" required aria-required="true"><?= \App\Utils\Helper::h($code_val) ?></textarea>
        </div>
        <div class="c-form__group">
            <label for="output_file" class="c-form__label">Replace output (optional)</label>
            <input type="file" id="output_file" name="output_file" class="c-form__input" accept="image/png,image/jpeg,application/pdf">
        </div>
        <div class="c-form__actions">
            <button type="submit" class="c-btn c-btn--primary">Save changes</button>
        </div>
    </form>
</section>
