<section class="page">
    <h1>Edit Lab Manual</h1>

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
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <label>
            Title
            <input type="text" name="title" required value="<?= \App\Core\View::h($title) ?>">
        </label>
        <label>
            Replace PDF (optional)
            <input type="file" name="manual_file" accept="application/pdf">
        </label>
        <button type="submit">Save changes</button>
    </form>
</section>
