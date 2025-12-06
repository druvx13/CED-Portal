<section class="page">
    <h1>Programming Languages</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Core\View::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2>Existing languages</h2>
    <table class="changes-table">
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
                <td><?= \App\Core\View::h($l['name']) ?></td>
                <td><?= \App\Core\View::h($l['slug']) ?></td>
                <td><?= \App\Core\View::h($l['created_at']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Delete this language?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$l['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add language</h2>
    <form method="post" class="form">
        <label>
            Language name
            <input type="text" name="name" placeholder="C++, Java, Python..." required>
        </label>
        <button type="submit" name="add" value="1">Add language</button>
    </form>
</section>
