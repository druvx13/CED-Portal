<!--
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
-->
<section class="page">
    <h1><?= \App\Utils\Helper::h($program['title']) ?></h1>
    <p class="muted">
        Language: <?= \App\Utils\Helper::h($program['language_name']) ?> |
        By: <?= \App\Utils\Helper::h($program['username'] ?? 'Unknown') ?> |
        <?= \App\Utils\Helper::h($program['created_at']) ?>
    </p>

    <?php if ($user && ($user['is_admin'] || (int)$user['id'] === (int)$program['uploaded_by'])): ?>
        <p>
            <a href="<?= BASE_URL ?>/lab-programs/edit?id=<?= (int)$program['id'] ?>">Edit</a>
            |
            <form method="post" action="<?= BASE_URL ?>/lab-programs/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this program?');">
                <input type="hidden" name="id" value="<?= (int)$program['id'] ?>">
                <button type="submit">Delete</button>
            </form>
        </p>
    <?php endif; ?>

    <div class="lab-program-layout">
        <div class="lab-program-code">
            <h2>Code</h2>
            <pre><code class="hljs language-<?= \App\Utils\Helper::h($program['language_slug']) ?>"><?= \App\Utils\Helper::h($program['code']) ?></code></pre>
        </div>
        <div class="lab-program-output">
            <h2>Output</h2>
            <?php if ($program['output_path']): ?>
                <?php if (str_ends_with(strtolower($program['output_path']), '.pdf')): ?>
                    <iframe src="<?= \App\Utils\Helper::h($program['output_path']) ?>" class="pdf-frame"></iframe>
                <?php else: ?>
                    <img src="<?= \App\Utils\Helper::h($program['output_path']) ?>" alt="Program output" class="output-preview">
                <?php endif; ?>
                <p><a href="<?= \App\Utils\Helper::h($program['output_path']) ?>" target="_blank">Download output</a></p>
            <?php else: ?>
                <p class="muted">No output uploaded.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
