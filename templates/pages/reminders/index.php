<!--
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
-->
<section class="page">
    <h1>Reminders</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <label>
            Message
            <input type="text" name="message" required value="<?= \App\Utils\Helper::h($message_val) ?>">
        </label>
        <label>
            Due date
            <input type="datetime-local" name="due_date" required value="<?= \App\Utils\Helper::h($due_date_val) ?>">
        </label>
        <button type="submit">Add reminder</button>
    </form>

    <h2>Your reminders</h2>
    <?php if ($reminders): ?>
        <ul class="reminder-list reminder-list--full">
            <?php foreach ($reminders as $r): ?>
                <li>
                    <div class="reminder-msg"><?= \App\Utils\Helper::h($r['message']) ?></div>
                    <div class="reminder-date"><?= \App\Utils\Helper::h($r['due_date']) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="muted">No reminders yet.</p>
    <?php endif; ?>
</section>
