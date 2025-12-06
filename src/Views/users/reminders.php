<section class="page">
    <h1>Reminders</h1>

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
            Message
            <input type="text" name="message" required value="<?= h($message ?? '') ?>">
        </label>
        <label>
            Due date
            <input type="datetime-local" name="due_date" required value="<?= h($due_date ?? '') ?>">
        </label>
        <button type="submit">Add reminder</button>
    </form>

    <h2>Your reminders</h2>
    <?php if ($reminders): ?>
        <ul class="reminder-list reminder-list--full">
            <?php foreach ($reminders as $r): ?>
                <li>
                    <div class="reminder-msg"><?= h($r['message']) ?></div>
                    <div class="reminder-date"><?= h($r['due_date']) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">No reminders yet.</p>
    <?php endif; ?>
</section>
