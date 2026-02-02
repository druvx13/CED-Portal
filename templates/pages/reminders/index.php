<section class="page">
    <h1>Reminders</h1>

    <?php if (!empty($errors)): ?>
        <div class="c-alert c-alert--error" role="alert">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="c-form">
        <?= \App\Utils\Helper::csrfField() ?>
        <div class="c-form__group">
            <label for="message" class="c-form__label">Message</label>
            <input type="text" id="message" name="message" class="c-form__input" required aria-required="true" autocomplete="off" value="<?= \App\Utils\Helper::h($message_val) ?>">
        </div>
        <div class="c-form__group">
            <label for="due_date" class="c-form__label">Due date</label>
            <input type="datetime-local" id="due_date" name="due_date" class="c-form__input" required aria-required="true" value="<?= \App\Utils\Helper::h($due_date_val) ?>">
        </div>
        <div class="c-form__actions">
            <button type="submit" class="c-btn c-btn--primary">Add reminder</button>
        </div>
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
        <p class="u-text-muted">No reminders yet.</p>
    <?php endif; ?>
</section>
