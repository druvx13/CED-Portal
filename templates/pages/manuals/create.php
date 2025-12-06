<section class="page">
    <h1>Upload Lab Manual</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Core\View::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form" enctype="multipart/form-data">
        <label>
            Title
            <input type="text" name="title" required value="<?= \App\Core\View::h($title) ?>">
        </label>
        <label>
            PDF file (max 10MB)
            <input type="file" name="manual_file" accept="application/pdf" required>
        </label>
        <button type="submit">Upload manual</button>
    </form>
</section>
