<!--
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
-->
<section class="page">
    <h1>Edit Lab Manual</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= \App\Utils\Helper::h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$manual['id'] ?>">
        <label>
            Title
            <input type="text" name="title" required value="<?= \App\Utils\Helper::h($title_val) ?>">
        </label>
        <label>
            Replace PDF (optional)
            <input type="file" name="manual_file" accept="application/pdf">
        </label>
        <button type="submit">Save changes</button>
    </form>
</section>
