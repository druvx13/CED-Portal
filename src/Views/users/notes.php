<section class="page">
    <h1>Notes</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <label>
            Title
            <input type="text" name="title" required value="<?= h($title ?? '') ?>">
        </label>
        <label>
            Body
            <textarea name="body" rows="6" required><?= h($body ?? '') ?></textarea>
        </label>
        <button type="submit">Add note</button>
    </form>

    <h2>Your notes</h2>
    <?php if ($notes): ?>
        <ul class="notes-list">
            <?php foreach ($notes as $n): ?>
                <li>
                    <h3><?= h($n['title']) ?></h3>
                    <div class="muted"><?= h($n['created_at']) ?></div>
                    <p><?= nl2br(h($n['body'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">No notes yet.</p>
    <?php endif; ?>
</section>
