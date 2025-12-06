<section class="page">
    <h1>Edit Lab Program</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Core\View::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <label>
            Title
            <input type="text" name="title" required value="<?= \App\Core\View::h($title) ?>">
        </label>
        <label>
            Language
            <select name="language_id" required>
                <option value="">Select language</option>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= (int)$lang['id'] ?>" <?= $language_id === (int)$lang['id'] ? 'selected' : '' ?>>
                        <?= \App\Core\View::h($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Code
            <textarea name="code" rows="15" class="textarea--mono" required><?= \App\Core\View::h($code) ?></textarea>
        </label>
        <label>
            Replace output (optional)
            <input type="file" name="output_file" accept="image/png,image/jpeg,application/pdf">
        </label>
        <button type="submit">Save changes</button>
    </form>
</section>
