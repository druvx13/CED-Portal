<section class="page">
    <h1><?= h($p['title']) ?></h1>
    <p class="muted">
        Language: <?= h($p['language_name']) ?> |
        By: <?= h($p['username'] ?? 'Unknown') ?> |
        <?= h($p['created_at']) ?>
    </p>

    <?php if (!empty($_SESSION['user_id']) && (!empty($_SESSION['is_admin']) || (int)$_SESSION['user_id'] === (int)$p['uploaded_by'])): ?>
        <p>
            <a href="<?= BASE_URL ?>/lab-programs/edit?id=<?= (int)$p['id'] ?>">Edit</a>
            |
            <form method="post" action="<?= BASE_URL ?>/lab-programs/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this program?');">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit">Delete</button>
            </form>
        </p>
    <?php endif; ?>

    <div class="lab-program-layout">
        <div class="lab-program-code">
            <h2>Code</h2>
            <pre><code class="hljs language-<?= h($p['language_slug']) ?>"><?= h($p['code']) ?></code></pre>
        </div>
        <div class="lab-program-output">
            <h2>Output</h2>
            <?php if ($p['output_path']): ?>
                <?php if (str_ends_with(strtolower($p['output_path']), '.pdf')): ?>
                    <iframe src="<?= h($p['output_path']) ?>" class="pdf-frame"></iframe>
                <?php else: ?>
                    <img src="<?= h($p['output_path']) ?>" alt="Program output" class="output-preview">
                <?php endif; ?>
                <p><a href="<?= h($p['output_path']) ?>" target="_blank">Download output</a></p>
            <?php else: ?>
                <p class="muted">No output uploaded.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
