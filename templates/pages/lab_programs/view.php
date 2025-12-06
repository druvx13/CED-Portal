<section class="page">
    <h1><?= \App\Core\View::h($p['title']) ?></h1>
    <p class="muted">
        Language: <?= \App\Core\View::h($p['language_name']) ?> |
        By: <?= \App\Core\View::h($p['username'] ?? 'Unknown') ?> |
        <?= \App\Core\View::h($p['created_at']) ?>
    </p>

    <?php if ($currentUser && ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$p['uploaded_by'])): ?>
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
            <pre><code class="hljs language-<?= \App\Core\View::h($p['language_slug']) ?>"><?= \App\Core\View::h($p['code']) ?></code></pre>
        </div>
        <div class="lab-program-output">
            <h2>Output</h2>
            <?php if ($p['output_path']): ?>
                <?php if (str_ends_with(strtolower($p['output_path']), '.pdf')): ?>
                    <iframe src="<?= \App\Core\View::h($p['output_path']) ?>" class="pdf-frame"></iframe>
                <?php else: ?>
                    <img src="<?= \App\Core\View::h($p['output_path']) ?>" alt="Program output" class="output-preview">
                <?php endif; ?>
                <p><a href="<?= \App\Core\View::h($p['output_path']) ?>" target="_blank">Download output</a></p>
            <?php else: ?>
                <p class="muted">No output uploaded.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
