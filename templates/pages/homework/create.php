<section class="page">
    <h1>Create Homework</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Core\View::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$subjects): ?>
        <p class="alert alert--error">No subjects defined. Add them under Admin → Subjects first.</p>
    <?php endif; ?>

    <form method="post" class="form" enctype="multipart/form-data">
        <label>
            Title
            <input type="text" name="title" required value="<?= \App\Core\View::h($title) ?>">
        </label>
        <label>
            Subject
            <select name="subject_id" required>
                <option value="">Select subject</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $subject_id === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= \App\Core\View::h($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Question
            <textarea name="question" rows="8" required><?= \App\Core\View::h($question) ?></textarea>
        </label>
        <label>
            Due date (optional)
            <input type="datetime-local" name="due_date" value="<?= \App\Core\View::h($due_date) ?>">
        </label>
        <label>
            Answer file (optional, PDF/DOC/IMG/ZIP, max 10MB)
            <input type="file" name="answer_file">
        </label>
        <button type="submit">Save</button>
    </form>
</section>
