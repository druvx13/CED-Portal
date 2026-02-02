<section class="page">
    <h1>Create Homework</h1>

    <?php if (!empty($errors)): ?>
        <div class="c-alert c-alert--error" role="alert">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$subjects): ?>
        <p class="c-alert c-alert--error" role="alert">No subjects defined. Add them under Admin → Subjects first.</p>
    <?php endif; ?>

    <form method="post" class="c-form" enctype="multipart/form-data">
        <?= \App\Utils\Helper::csrfField() ?>
        <div class="c-form__group">
            <label for="title" class="c-form__label">Title</label>
            <input type="text" id="title" name="title" class="c-form__input" required aria-required="true" autocomplete="off" value="<?= \App\Utils\Helper::h($title_val) ?>">
        </div>
        <div class="c-form__group">
            <label for="subject_id" class="c-form__label">Subject</label>
            <select id="subject_id" name="subject_id" class="c-form__select" required aria-required="true">
                <option value="">Select subject</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $subject_id_val === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= \App\Utils\Helper::h($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="c-form__group">
            <label for="question" class="c-form__label">Question</label>
            <textarea id="question" name="question" rows="8" class="c-form__textarea" required aria-required="true"><?= \App\Utils\Helper::h($question_val) ?></textarea>
        </div>
        <div class="c-form__group">
            <label for="due_date" class="c-form__label">Due date (optional)</label>
            <input type="datetime-local" id="due_date" name="due_date" class="c-form__input" value="<?= \App\Utils\Helper::h($due_date_val) ?>">
        </div>
        <div class="c-form__group">
            <label for="answer_file" class="c-form__label">Answer file (optional, PDF/DOC/IMG/ZIP, max 10MB)</label>
            <input type="file" id="answer_file" name="answer_file" class="c-form__input">
        </div>
        <div class="c-form__actions">
            <button type="submit" class="c-btn c-btn--primary">Save</button>
        </div>
    </form>
</section>
