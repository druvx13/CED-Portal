<section class="page">
    <h1>Subjects</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2>Existing subjects</h2>
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
        <?php foreach ($subjects as $s): ?>
            <tr>
                <td><?= (int)$s['id'] ?></td>
                <td><?= h($s['name']) ?></td>
                <td><?= h($s['slug']) ?></td>
                <td><?= h($s['created_at']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Delete this subject?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$s['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add subject</h2>
    <form method="post" class="form">
        <label>
            Subject name
            <input type="text" name="name" placeholder="DSA, DBMS, OS..." required>
        </label>
        <button type="submit" name="add" value="1">Add subject</button>
    </form>
</section>
