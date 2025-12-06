<section class="page">
    <h1>Upload Lab Program</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$languages): ?>
        <p class="alert alert--error">
            No languages defined. Add them in Admin → Languages first.
        </p>
    <?php endif; ?>

    <form method="post" class="form" enctype="multipart/form-data">
        <label>
            Title
            <input type="text" name="title" required value="<?= \App\Utils\Helper::h($title_val) ?>">
        </label>
        <label>
            Language
            <select name="language_id" required>
                <option value="">Select language</option>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= (int)$lang['id'] ?>" <?= $language_id_val === (int)$lang['id'] ? 'selected' : '' ?>>
                        <?= \App\Utils\Helper::h($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Code
            <textarea name="code" rows="15" class="textarea--mono" required><?= \App\Utils\Helper::h($code_val) ?></textarea>
        </label>
        <label>
            Output (image or PDF, optional, max 10MB)
            <input type="file" name="output_file" accept="image/png,image/jpeg,application/pdf">
        </label>
        <button type="submit">Save program</button>
    </form>
</section>
