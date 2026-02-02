<section class="page">
    <h1>Programming Languages</h1>

    <?php if (!empty($errors)): ?>
        <div class="c-alert c-alert--error" role="alert">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2>Existing languages</h2>
    <table class="c-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($langs as $l): ?>
            <tr>
                <td><?= (int)$l['id'] ?></td>
                <td><?= \App\Utils\Helper::h($l['name']) ?></td>
                <td><?= \App\Utils\Helper::h($l['slug']) ?></td>
                <td><?= \App\Utils\Helper::h($l['created_at']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Delete this language?');">
                        <?= \App\Utils\Helper::csrfField() ?>
                        <input type="hidden" name="delete_id" value="<?= (int)$l['id'] ?>">
                        <button type="submit" class="c-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add language</h2>
    <form method="post" class="c-form">
        <?= \App\Utils\Helper::csrfField() ?>
        <div class="c-form__group">
            <label for="name" class="c-form__label">Language name</label>
            <input type="text" id="name" name="name" class="c-form__input" placeholder="C++, Java, Python..." required aria-required="true" autocomplete="off">
        </div>
        <div class="c-form__actions">
            <button type="submit" name="add" value="1" class="c-btn c-btn--primary">Add language</button>
        </div>
    </form>
</section>
