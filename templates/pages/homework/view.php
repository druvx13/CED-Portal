<section class="page">
    <h1><?= \App\Utils\Helper::h($hw['title']) ?></h1>
    <p class="muted">
        <strong>Subject:</strong> <?= \App\Utils\Helper::h($hw['subject_name']) ?> |
        <strong>Due Date:</strong> <?= \App\Utils\Helper::h($hw['due_date'] ?? 'No due date') ?>
    </p>

    <div class="card">
        <h3>Question</h3>
        <div class="content">
            <?= nl2br(\App\Utils\Helper::h($hw['question'])) ?>
        </div>
    </div>

    <?php if ($hw['answer_path']): ?>
        <div class="card">
            <h3>Answer</h3>
            <p>
                <?php if (!$hw['due_date'] || $hw['due_date'] <= date('Y-m-d H:i:s')): ?>
                    <a href="<?= \App\Utils\Helper::h($hw['answer_path']) ?>" target="_blank" class="btn">Download Answer</a>
                <?php else: ?>
                    <span class="muted">Answer is locked until the due date.</span>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="actions">
        <?php if ($user && ($user['is_admin'] || (int)$user['id'] === (int)$hw['uploaded_by'])): ?>
            <a href="<?= BASE_URL ?>/homework/edit?id=<?= (int)$hw['id'] ?>" class="btn">Edit</a>
            <form method="post" action="<?= BASE_URL ?>/homework/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this homework?');">
                <input type="hidden" name="id" value="<?= (int)$hw['id'] ?>">
                <button type="submit" class="btn btn--danger">Delete</button>
            </form>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/homework" class="btn btn--secondary">Back to Homework</a>
    </div>
</section>
