<?php
// index.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';

$pdo = db();
bootstrap_initial_admin($pdo);

/* --------- Ensure upload directories --------- */

$uploadBase = __DIR__ . '/uploads';
$dirs = [
    $uploadBase,
    $uploadBase . '/code_outputs',
    $uploadBase . '/manuals',
    $uploadBase . '/homework_answers',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

/* --------- Load current user --------- */

$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, username, is_admin, is_first_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch() ?: null;
}

/* --------- Helpers --------- */

function redirect(string $path): void {
    if (strpos($path, 'http') !== 0) {
        $path = rtrim(BASE_URL, '/') . $path;
    }
    header('Location: ' . $path);
    exit;
}

function require_login(): void {
    global $currentUser;
    if (!$currentUser) {
        http_response_code(401);
        redirect('/login');
    }
}

function require_admin(): void {
    global $currentUser;
    if (!$currentUser || !$currentUser['is_admin']) {
        http_response_code(403);
        echo "403 Forbidden";
        exit;
    }
}

function require_superadmin(): void {
    global $currentUser;
    if (
        !$currentUser ||
        (int)$currentUser['id'] !== 1 ||
        !(int)$currentUser['is_first_admin']
    ) {
        http_response_code(403);
        echo "403 Forbidden: Only initial admin can perform this action.";
        exit;
    }
}

function safe_slug(string $name): string {
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    if ($slug === '') {
        $slug = bin2hex(random_bytes(4));
    }
    return $slug;
}

function render_header(string $title): void {
    global $currentUser;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= h($title) ?> - CED Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <!-- Highlight.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>document.addEventListener('DOMContentLoaded', () => { if (window.hljs) { hljs.highlightAll(); } });</script>
</head>
<body>
<header class="site-header">
    <div class="site-header__inner">
        <a href="<?= BASE_URL ?>/" class="logo">CED Portal</a>

        <nav class="nav">
            <a href="<?= BASE_URL ?>/">Dashboard</a>
            <a href="<?= BASE_URL ?>/lab-programs">Lab Programs</a>
            <a href="<?= BASE_URL ?>/manuals">Lab Manuals</a>
            <a href="<?= BASE_URL ?>/homework">Homework</a>
            <a href="<?= BASE_URL ?>/users">Users</a>
            <?php if ($currentUser): ?>
                <a href="<?= BASE_URL ?>/reminders">Reminders</a>
                <a href="<?= BASE_URL ?>/notes">Notes</a>
                <?php if ($currentUser['is_admin']): ?>
                    <a href="<?= BASE_URL ?>/admin/users">Admin</a>
                    <a href="<?= BASE_URL ?>/admin/languages">Languages</a>
                    <a href="<?= BASE_URL ?>/admin/subjects">Subjects</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="auth">
            <?php if ($currentUser): ?>
                <span class="auth__user">
                    <?= h($currentUser['username']) ?><?= $currentUser['is_admin'] ? ' (admin)' : '' ?>
                </span>
                <form method="post" action="<?= BASE_URL ?>/logout" class="auth__form">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
    <?php
}

function render_footer(): void {
    ?>
</main>
<footer class="site-footer">
    <p>
  <strong>Disclaimer:</strong> I, <strong style="color: #FF0000;">Nikol</strong>, am a Diploma student in Computer Engineering. The content I share originates from materials accessible through my academic program and represents my coursework, projects, or personal study—shared strictly for educational purposes.
	</p>

	<center><p>
  <img src="https://nikol.22web.org/img/members/2/mini_cooltext497354994916342.png" alt="Nikol Logo">
	</p>

	<p>
  All content herein has been uploaded by <strong style="color: #FF0000;">NIKOL</strong>.
	</p></center>
</footer>
<script src="<?= BASE_URL ?>/assets/app.js"></script>
</body>
</html>
    <?php
}

/**
 * File upload helper with MIME validation and max size 10MB.
 * Returns web path (relative to BASE_URL) or throws RuntimeException.
 */
function handle_file_upload(string $field, string $context): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload error: ' . (int)$file['error']);
    }

    $maxSize = 10 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        http_response_code(413);
        throw new RuntimeException('File too large (max 10MB).');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';

    $ext = '';
    $baseDir = __DIR__ . '/uploads';
    $webBase = '/uploads';

    if ($context === 'code_output') {
        $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'application/pdf' => 'pdf'];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Invalid output file type.');
        }
        $ext = '.' . $allowed[$mime];
        $targetDir = $baseDir . '/code_outputs';
        $webDir    = $webBase . '/code_outputs';
    } elseif ($context === 'manual') {
        if ($mime !== 'application/pdf') {
            throw new RuntimeException('Manuals must be PDF.');
        }
        $ext = '.pdf';
        $targetDir = $baseDir . '/manuals';
        $webDir    = $webBase . '/manuals';
    } elseif ($context === 'homework_answer') {
        $allowed = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip'
        ];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Unsupported answer format.');
        }
        $ext = '.' . $allowed[$mime];
        $targetDir = $baseDir . '/homework_answers';
        $webDir    = $webBase . '/homework_answers';
    } else {
        throw new RuntimeException('Invalid upload context.');
    }

    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    $basename = bin2hex(random_bytes(8)) . $ext;
    $targetFs = $targetDir . '/' . $basename;
    $webPath  = $webDir . '/' . $basename;

    if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
        throw new RuntimeException('Failed to store uploaded file.');
    }

    return $webPath;
}

/* --------- Routing --------- */

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

/* ---- Auth routes ---- */

if ($path === '/login') {
    if ($currentUser) {
        redirect('/');
    }

    if ($method === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = null;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid credentials.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            redirect('/');
        }
    }

    render_header('Login');
    ?>
    <section class="page auth-page">
        <h1>Login</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert--error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="post" class="form">
            <label>
                Username
                <input type="text" name="username" required>
            </label>
            <label>
                Password
                <input type="password" name="password" required>
            </label>
            <button type="submit">Login</button>
        </form>
        <!--
        <p class="muted">
            First run: username <code>admin</code>, password <code>ChangeMe123!</code>
            (change in DB after logging in).
        </p>
        -->
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/logout' && $method === 'POST') {
    session_destroy();
    redirect('/');
}

/* ---- Dashboard (home) – visible to guests ---- */

if ($path === '/' || $path === '/dashboard') {

    // For logged-in user: reminders
    $reminders = [];
    if ($currentUser) {
        $stmt = $pdo->prepare("
            SELECT id, message, due_date
            FROM reminders
            WHERE user_id = ? AND due_date >= NOW()
            ORDER BY due_date ASC
            LIMIT 3
        ");
        $stmt->execute([$currentUser['id']]);
        $reminders = $stmt->fetchAll();
    }

    // Recent lab programs (language category with fallback to old column)
    $stmt = $pdo->query("
        SELECT
            lp.id,
            lp.title,
            lp.code,
            lp.created_at,
            lp.uploaded_by,
            u.username,
            COALESCE(pl.name, lp.language) AS language_name,
            COALESCE(pl.slug, lp.language) AS language_slug
        FROM lab_programs lp
        LEFT JOIN programming_languages pl ON lp.language_id = pl.id
        LEFT JOIN users u ON lp.uploaded_by = u.id
        ORDER BY lp.created_at DESC
        LIMIT 5
    ");
    $programs = $stmt->fetchAll();

    // Recent manuals
    $stmt = $pdo->query("
        SELECT id, title, pdf_path, created_at
        FROM lab_manuals
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $manuals = $stmt->fetchAll();

    render_header('Dashboard');
    ?>
    <section class="page dashboard">
        <h1>Dashboard</h1>
        <p class="muted">
            Guests can browse content. Log in to add your own lab programs, manuals, homework and reminders.
        </p>

        <div class="dashboard-grid">
            <?php if ($currentUser): ?>
                <section class="card card--reminders">
                    <header class="card__header">
                        <h2>Upcoming Reminders</h2>
                        <a href="<?= BASE_URL ?>/reminders" class="card__link">View all</a>
                    </header>
                    <?php if ($reminders): ?>
                        <ul class="reminder-list">
                            <?php foreach ($reminders as $r): ?>
                                <li>
                                    <div class="reminder-msg"><?= h($r['message']) ?></div>
                                    <div class="reminder-date"><?= h($r['due_date']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="muted">No upcoming reminders.</p>
                    <?php endif; ?>
                    <a class="btn btn--small" href="<?= BASE_URL ?>/reminders">Add reminder</a>
                </section>
            <?php endif; ?>

            <section class="card card--programs">
                <header class="card__header">
                    <h2>Recent Lab Programs</h2>
                    <a href="<?= BASE_URL ?>/lab-programs" class="card__link">All programs</a>
                </header>
                <?php if ($programs): ?>
                    <div class="code-snippet-list">
                        <?php foreach ($programs as $p): ?>
                            <article class="code-snippet">
                                <header>
                                    <h3>
                                        <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                                            <?= h($p['title']) ?>
                                        </a>
                                    </h3>
                                    <span class="badge"><?= h($p['language_name']) ?></span>
                                </header>
                                <pre><code class="hljs language-<?= h($p['language_slug']) ?>"><?php
                                    $preview = mb_substr($p['code'], 0, 260, 'UTF-8');
                                    echo h($preview . (mb_strlen($p['code'], 'UTF-8') > 260 ? "...\n" : ""));
                                ?></code></pre>
                                <div class="code-snippet-meta">
                                    <span class="muted">By <?= h($p['username'] ?? 'Unknown') ?></span>
                                    <span class="muted"><?= h($p['created_at']) ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted">No programs yet.</p>
                <?php endif; ?>
                <?php if ($currentUser): ?>
                    <a class="btn btn--small" href="<?= BASE_URL ?>/lab-programs/new">Upload program</a>
                <?php endif; ?>
            </section>

            <section class="card card--manuals">
                <header class="card__header">
                    <h2>Latest Lab Manuals</h2>
                    <a href="<?= BASE_URL ?>/manuals" class="card__link">All manuals</a>
                </header>
                <?php if ($manuals): ?>
                    <ul class="manual-list">
                        <?php foreach ($manuals as $m): ?>
                            <li>
                                <a href="<?= h($m['pdf_path']) ?>" target="_blank">
                                    <?= h($m['title']) ?>
                                </a>
                                <span class="muted"><?= h($m['created_at']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="muted">No manuals yet.</p>
                <?php endif; ?>
                <?php if ($currentUser): ?>
                    <a class="btn btn--small" href="<?= BASE_URL ?>/manuals/new">Upload manual</a>
                <?php endif; ?>
            </section>
        </div>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- Admin: user management ---- */

if ($path === '/admin/users') {
    require_login();
    require_admin();

    $errors = [];
    $success = null;

    if ($method === 'POST') {
        require_superadmin(); // Only first admin can create users

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $is_admin = !empty($_POST['is_admin']) ? 1 : 0;

        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username already taken.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password_hash, is_admin, is_first_admin, created_by)
                        VALUES (?, ?, ?, 0, ?)
                    ");
                    $stmt->execute([$username, $hash, $is_admin, $currentUser['id']]);

                    $newId = (int)$pdo->lastInsertId();

                    $stmt = $pdo->prepare("
                        INSERT INTO user_audit (action, target_user_id, admin_id)
                        VALUES ('create', ?, ?)
                    ");
                    $stmt->execute([$newId, $currentUser['id']]);

                    $pdo->commit();
                    $success = "User created (ID: {$newId}).";
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    $errors[] = "Error creating user.";
                }
            }
        }
    }

    $users = $pdo->query("
        SELECT id, username, is_admin, is_first_admin, created_at
        FROM users
        ORDER BY id ASC
    ")->fetchAll();

    render_header('Admin - Users');
    ?>
    <section class="page">
        <h1>User Management</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="notice"><?= h($success) ?></p>
        <?php endif; ?>

        <?php if ((int)$currentUser['id'] === 1 && $currentUser['is_first_admin']): ?>
            <h2>Create user</h2>
            <form method="post" class="form">
                <label>
                    Username
                    <input type="text" name="username" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" required>
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_admin" value="1">
                    Make admin
                </label>
                <button type="submit">Create user</button>
            </form>
        <?php else: ?>
            <p class="muted">Only the first admin (ID=1) can create users.</p>
        <?php endif; ?>

        <h2>Existing users</h2>
        <table class="changes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Admin</th>
                    <th>First Admin</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= h($u['username']) ?></td>
                    <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
                    <td><?= $u['is_first_admin'] ? 'Yes' : 'No' ?></td>
                    <td><?= h($u['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- Admin: programming languages ---- */

if ($path === '/admin/languages') {
    require_login();
    require_admin();

    $errors = [];

    if ($method === 'POST') {
        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $errors[] = "Language name is required.";
            } else {
                $slug = safe_slug($name);
                $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
                $stmt->execute([$name]);
                if ($stmt->fetch()) {
                    $errors[] = "Language already exists.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO programming_languages (name, slug)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$name, $slug]);
                }
            }
        } elseif (isset($_POST['delete_id'])) {
            $id = (int)$_POST['delete_id'];
            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_programs WHERE language_id = ?");
            $stmt->execute([$id]);
            $c = (int)$stmt->fetch()['c'];
            if ($c > 0) {
                $errors[] = "Cannot delete language that is used by lab programs.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM programming_languages WHERE id = ?");
                $stmt->execute([$id]);
            }
        }
    }

    $langs = $pdo->query("
        SELECT id, name, slug, created_at
        FROM programming_languages
        ORDER BY name ASC
    ")->fetchAll();

    render_header('Admin - Languages');
    ?>
    <section class="page">
        <h1>Programming Languages</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <h2>Existing languages</h2>
        <table class="changes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($langs as $l): ?>
                <tr>
                    <td><?= (int)$l['id'] ?></td>
                    <td><?= h($l['name']) ?></td>
                    <td><?= h($l['slug']) ?></td>
                    <td><?= h($l['created_at']) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this language?');">
                            <input type="hidden" name="delete_id" value="<?= (int)$l['id'] ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Add language</h2>
        <form method="post" class="form">
            <label>
                Language name
                <input type="text" name="name" placeholder="C++, Java, Python..." required>
            </label>
            <button type="submit" name="add" value="1">Add language</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- Admin: subjects ---- */

if ($path === '/admin/subjects') {
    require_login();
    require_admin();

    $errors = [];

    if ($method === 'POST') {
        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $errors[] = "Subject name is required.";
            } else {
                $slug = safe_slug($name);
                $stmt = $pdo->prepare("SELECT id FROM subjects WHERE name = ?");
                $stmt->execute([$name]);
                if ($stmt->fetch()) {
                    $errors[] = "Subject already exists.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO subjects (name, slug)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$name, $slug]);
                }
            }
        } elseif (isset($_POST['delete_id'])) {
            $id = (int)$_POST['delete_id'];
            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM homework WHERE subject_id = ?");
            $stmt->execute([$id]);
            $c = (int)$stmt->fetch()['c'];
            if ($c > 0) {
                $errors[] = "Cannot delete subject that is used by homework.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
                $stmt->execute([$id]);
            }
        }
    }

    $subjects = $pdo->query("
        SELECT id, name, slug, created_at
        FROM subjects
        ORDER BY name ASC
    ")->fetchAll();

    render_header('Admin - Subjects');
    ?>
    <section class="page">
        <h1>Subjects</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <h2>Existing subjects</h2>
        <table class="changes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $s): ?>
                <tr>
                    <td><?= (int)$s['id'] ?></td>
                    <td><?= h($s['name']) ?></td>
                    <td><?= h($s['slug']) ?></td>
                    <td><?= h($s['created_at']) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this subject?');">
                            <input type="hidden" name="delete_id" value="<?= (int)$s['id'] ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Add subject</h2>
        <form method="post" class="form">
            <label>
                Subject name
                <input type="text" name="name" placeholder="DSA, DBMS, OS..." required>
            </label>
            <button type="submit" name="add" value="1">Add subject</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- Lab programs ---- */

if ($path === '/lab-programs') {
    // visible to guests, with pagination and per-language grouping
    $perPage = 20;
    $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM lab_programs")->fetch()['c'];
    $totalPages = max(1, (int)ceil($total / $perPage));

    $sql = "
        SELECT
            lp.id,
            lp.title,
            lp.code,
            lp.created_at,
            lp.uploaded_by,
            u.username,
            COALESCE(pl.name, lp.language) AS language_name,
            COALESCE(pl.slug, lp.language) AS language_slug
        FROM lab_programs lp
        LEFT JOIN programming_languages pl ON lp.language_id = pl.id
        LEFT JOIN users u ON lp.uploaded_by = u.id
        ORDER BY language_name ASC, lp.created_at DESC
        LIMIT $perPage OFFSET $offset
    ";
    $rows = $pdo->query($sql)->fetchAll();

    $grouped = [];
    foreach ($rows as $r) {
        $grouped[$r['language_name']][] = $r;
    }

    render_header('Lab Programs');
    ?>
    <section class="page">
        <h1>Lab Programs</h1>
        <?php if ($currentUser): ?>
            <p><a class="btn btn--small" href="<?= BASE_URL ?>/lab-programs/new">Upload new program</a></p>
        <?php else: ?>
            <p class="muted">Login to upload new programs. Existing ones are visible to everyone.</p>
        <?php endif; ?>

        <?php if (!$grouped): ?>
            <p class="muted">No lab programs uploaded yet.</p>
        <?php else: ?>
            <?php foreach ($grouped as $language => $programs): ?>
                <section class="card">
                    <h2><?= h($language) ?></h2>
                    <ul>
                        <?php foreach ($programs as $p): ?>
                            <li>
                                <strong>
                                    <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                                        <?= h($p['title']) ?>
                                    </a>
                                </strong>
                                <span class="muted">
                                    by <?= h($p['username'] ?? 'Unknown') ?> · <?= h($p['created_at']) ?>
                                </span>
                                <?php if ($currentUser && ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$p['uploaded_by'])): ?>
                                    <span>
                                        |
                                        <a href="<?= BASE_URL ?>/lab-programs/edit?id=<?= (int)$p['id'] ?>">Edit</a>
                                        <form method="post" action="<?= BASE_URL ?>/lab-programs/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this program?');">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    </span>
                                <?php endif; ?>
                                <details>
                                    <summary>Code preview</summary>
                                    <pre><code class="hljs language-<?= h($p['language_slug']) ?>"><?= h(mb_strimwidth($p['code'], 0, 500, "\n…", 'UTF-8')) ?></code></pre>
                                </details>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL ?>/lab-programs?page=<?= $page - 1 ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL ?>/lab-programs?page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/lab-programs/new') {
    require_login();

    $errors = [];
    $title = $code = '';
    $language_id = 0;

    $languages = $pdo->query("
        SELECT id, name, slug
        FROM programming_languages
        ORDER BY name ASC
    ")->fetchAll();

    if ($method === 'POST') {
        $title       = trim($_POST['title'] ?? '');
        $code        = (string)($_POST['code'] ?? '');
        $language_id = (int)($_POST['language_id'] ?? 0);

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if (mb_strlen($code, 'UTF-8') < 5) {
            $errors[] = "Code too short.";
        }
        if ($language_id <= 0) {
            $errors[] = "Language must be selected.";
        }

        $chosenLangSlug = null;
        if ($language_id > 0) {
            foreach ($languages as $l) {
                if ((int)$l['id'] === $language_id) {
                    $chosenLangSlug = $l['slug'];
                    break;
                }
            }
        }

        $outputPath = null;
        if (!$errors) {
            try {
                $outputPath = handle_file_upload('output_file', 'code_output');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $storedLanguageString = $chosenLangSlug ?: 'unknown';

            $stmt = $pdo->prepare("
                INSERT INTO lab_programs (title, code, language, language_id, output_path, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $code,
                $storedLanguageString,
                $language_id,
                $outputPath,
                $currentUser['id']
            ]);
            redirect('/lab-programs');
        }
    }

    render_header('New Lab Program');
    ?>
    <section class="page">
        <h1>Upload Lab Program</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!$languages): ?>
            <p class="alert alert--error">
                No languages defined. Add them in Admin → Languages first.
            </p>
        <?php endif; ?>

        <form method="post" class="form" enctype="multipart/form-data">
            <label>
                Title
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                Language
                <select name="language_id" required>
                    <option value="">Select language</option>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?= (int)$lang['id'] ?>" <?= $language_id === (int)$lang['id'] ? 'selected' : '' ?>>
                            <?= h($lang['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Code
                <textarea name="code" rows="15" class="textarea--mono" required><?= h($code) ?></textarea>
            </label>
            <label>
                Output (image or PDF, optional, max 10MB)
                <input type="file" name="output_file" accept="image/png,image/jpeg,application/pdf">
            </label>
            <button type="submit">Save program</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/lab-programs/edit') {
    require_login();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        render_header('Bad request');
        echo '<section class="page"><h1>Bad request</h1></section>';
        render_footer();
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM lab_programs WHERE id = ?");
    $stmt->execute([$id]);
    $program = $stmt->fetch();

    if (!$program) {
        http_response_code(404);
        render_header('Program not found');
        echo '<section class="page"><h1>Program not found</h1></section>';
        render_footer();
        exit;
    }

    if (!$currentUser['is_admin'] && (int)$currentUser['id'] !== (int)$program['uploaded_by']) {
        http_response_code(403);
        render_header('Forbidden');
        echo '<section class="page"><h1>403 Forbidden</h1></section>';
        render_footer();
        exit;
    }

    $errors = [];
    $title       = $program['title'];
    $code        = $program['code'];
    $language_id = (int)($program['language_id'] ?? 0);
    $outputPath  = $program['output_path'];

    $languages = $pdo->query("
        SELECT id, name, slug
        FROM programming_languages
        ORDER BY name ASC
    ")->fetchAll();

    if ($method === 'POST') {
        $title       = trim($_POST['title'] ?? '');
        $code        = (string)($_POST['code'] ?? '');
        $language_id = (int)($_POST['language_id'] ?? 0);

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if (mb_strlen($code, 'UTF-8') < 5) {
            $errors[] = "Code too short.";
        }
        if ($language_id <= 0) {
            $errors[] = "Language must be selected.";
        }

        $chosenLangSlug = null;
        if ($language_id > 0) {
            foreach ($languages as $l) {
                if ((int)$l['id'] === $language_id) {
                    $chosenLangSlug = $l['slug'];
                    break;
                }
            }
        }

        if (!$errors) {
            try {
                $newOutput = handle_file_upload('output_file', 'code_output');
                if ($newOutput !== null) {
                    $outputPath = $newOutput;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $storedLanguageString = $chosenLangSlug ?: $program['language'];

            $stmt = $pdo->prepare("
                UPDATE lab_programs
                SET title = ?, code = ?, language = ?, language_id = ?, output_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title,
                $code,
                $storedLanguageString,
                $language_id,
                $outputPath,
                $id
            ]);
            redirect('/lab-programs/view?id=' . $id);
        }
    }

    render_header('Edit Lab Program');
    ?>
    <section class="page">
        <h1>Edit Lab Program</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <label>
                Title
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                Language
                <select name="language_id" required>
                    <option value="">Select language</option>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?= (int)$lang['id'] ?>" <?= $language_id === (int)$lang['id'] ? 'selected' : '' ?>>
                            <?= h($lang['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Code
                <textarea name="code" rows="15" class="textarea--mono" required><?= h($code) ?></textarea>
            </label>
            <label>
                Replace output (optional)
                <input type="file" name="output_file" accept="image/png,image/jpeg,application/pdf">
            </label>
            <button type="submit">Save changes</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/lab-programs/delete' && $method === 'POST') {
    require_login();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        redirect('/lab-programs');
    }

    $stmt = $pdo->prepare("SELECT uploaded_by FROM lab_programs WHERE id = ?");
    $stmt->execute([$id]);
    $program = $stmt->fetch();

    if ($program && ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$program['uploaded_by'])) {
        $stmt = $pdo->prepare("DELETE FROM lab_programs WHERE id = ?");
        $stmt->execute([$id]);
    }

    redirect('/lab-programs');
}

if ($path === '/lab-programs/view') {
    // viewable by guests
    $id = (int)($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("
        SELECT
            lp.*,
            u.username,
            COALESCE(pl.name, lp.language) AS language_name,
            COALESCE(pl.slug, lp.language) AS language_slug
        FROM lab_programs lp
        LEFT JOIN programming_languages pl ON lp.language_id = pl.id
        LEFT JOIN users u ON lp.uploaded_by = u.id
        WHERE lp.id = ?
    ");
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) {
        http_response_code(404);
        render_header('Program not found');
        echo "<section class=\"page\"><h1>Program not found</h1></section>";
        render_footer();
        exit;
    }

    render_header('Lab Program - ' . $p['title']);
    ?>
    <section class="page">
        <h1><?= h($p['title']) ?></h1>
        <p class="muted">
            Language: <?= h($p['language_name']) ?> |
            By: <?= h($p['username'] ?? 'Unknown') ?> |
            <?= h($p['created_at']) ?>
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
    <?php
    render_footer();
    exit;
}

/* ---- Manuals ---- */

if ($path === '/manuals') {
    // visible to guests with pagination
    $perPage = 20;
    $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM lab_manuals")->fetch()['c'];
    $totalPages = max(1, (int)ceil($total / $perPage));

    $sql = "
        SELECT lm.*, u.username
        FROM lab_manuals lm
        LEFT JOIN users u ON lm.uploaded_by = u.id
        ORDER BY lm.created_at DESC
        LIMIT $perPage OFFSET $offset
    ";
    $manuals = $pdo->query($sql)->fetchAll();

    render_header('Lab Manuals');
    ?>
    <section class="page">
        <h1>Lab Manuals</h1>
        <?php if ($currentUser): ?>
            <p><a class="btn btn--small" href="<?= BASE_URL ?>/manuals/new">Upload manual</a></p>
        <?php else: ?>
            <p class="muted">Login to upload manuals. Everyone can download them.</p>
        <?php endif; ?>

        <?php if ($manuals): ?>
            <ul class="manual-list">
                <?php foreach ($manuals as $m): ?>
                    <li>
                        <a href="<?= h($m['pdf_path']) ?>" target="_blank">
                            <?= h($m['title']) ?>
                        </a>
                        <span class="muted">
                            (<?= h($m['created_at']) ?>,
                            by <?= h($m['username'] ?? 'Unknown') ?>)
                        </span>
                        <?php if ($currentUser && ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$m['uploaded_by'])): ?>
                            <span>
                                |
                                <a href="<?= BASE_URL ?>/manuals/edit?id=<?= (int)$m['id'] ?>">Edit</a>
                                <form method="post" action="<?= BASE_URL ?>/manuals/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this manual?');">
                                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL ?>/manuals?page=<?= $page - 1 ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL ?>/manuals?page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <p class="muted">No manuals uploaded yet.</p>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/manuals/new') {
    require_login();

    $errors = [];
    $title  = '';

    if ($method === 'POST') {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $errors[] = "Title is required.";
        }

        $pdfPath = null;
        if (!$errors) {
            try {
                $pdfPath = handle_file_upload('manual_file', 'manual');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors && $pdfPath) {
            $stmt = $pdo->prepare("
                INSERT INTO lab_manuals (title, pdf_path, uploaded_by)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$title, $pdfPath, $currentUser['id']]);
            redirect('/manuals');
        }
    }

    render_header('New Lab Manual');
    ?>
    <section class="page">
        <h1>Upload Lab Manual</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form" enctype="multipart/form-data">
            <label>
                Title
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                PDF file (max 10MB)
                <input type="file" name="manual_file" accept="application/pdf" required>
            </label>
            <button type="submit">Upload manual</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/manuals/edit') {
    require_login();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        render_header('Bad request');
        echo '<section class="page"><h1>Bad request</h1></section>';
        render_footer();
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM lab_manuals WHERE id = ?");
    $stmt->execute([$id]);
    $manual = $stmt->fetch();

    if (!$manual) {
        http_response_code(404);
        render_header('Manual not found');
        echo '<section class="page"><h1>Manual not found</h1></section>';
        render_footer();
        exit;
    }

    if (!$currentUser['is_admin'] && (int)$currentUser['id'] !== (int)$manual['uploaded_by']) {
        http_response_code(403);
        render_header('Forbidden');
        echo '<section class="page"><h1>403 Forbidden</h1></section>';
        render_footer();
        exit;
    }

    $errors = [];
    $title   = $manual['title'];
    $pdfPath = $manual['pdf_path'];

    if ($method === 'POST') {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $errors[] = "Title is required.";
        }

        if (!$errors) {
            try {
                $newPdf = handle_file_upload('manual_file', 'manual');
                if ($newPdf !== null) {
                    $pdfPath = $newPdf;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE lab_manuals
                SET title = ?, pdf_path = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $pdfPath, $id]);
            redirect('/manuals');
        }
    }

    render_header('Edit Lab Manual');
    ?>
    <section class="page">
        <h1>Edit Lab Manual</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <label>
                Title
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                Replace PDF (optional)
                <input type="file" name="manual_file" accept="application/pdf">
            </label>
            <button type="submit">Save changes</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/manuals/delete' && $method === 'POST') {
    require_login();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        redirect('/manuals');
    }

    $stmt = $pdo->prepare("SELECT uploaded_by FROM lab_manuals WHERE id = ?");
    $stmt->execute([$id]);
    $manual = $stmt->fetch();

    if ($manual && ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$manual['uploaded_by'])) {
        $stmt = $pdo->prepare("DELETE FROM lab_manuals WHERE id = ?");
        $stmt->execute([$id]);
    }

    redirect('/manuals');
}

/* ---- Homework ---- */

if ($path === '/homework') {
    // visible to guests, subject-wise grouping + pagination on overall list
    $perPage = 20;
    $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM homework")->fetch()['c'];
    $totalPages = max(1, (int)ceil($total / $perPage));

    $sql = "
        SELECT
            h.*,
            u.username,
            COALESCE(s.name, 'Uncategorized') AS subject_name
        FROM homework h
        LEFT JOIN subjects s ON h.subject_id = s.id
        LEFT JOIN users u ON h.uploaded_by = u.id
        ORDER BY subject_name ASC, h.created_at DESC
        LIMIT $perPage OFFSET $offset
    ";
    $homework = $pdo->query($sql)->fetchAll();

    $grouped = [];
    foreach ($homework as $hw) {
        $grouped[$hw['subject_name']][] = $hw;
    }

    render_header('Homework');
    ?>
    <section class="page">
        <h1>Homework</h1>
        <?php if ($currentUser && $currentUser['is_admin']): ?>
            <p><a class="btn btn--small" href="<?= BASE_URL ?>/homework/new">Create homework</a></p>
        <?php else: ?>
            <p class="muted">Homework questions are visible to everyone. Only staff can post new ones.</p>
        <?php endif; ?>

        <?php if (!$grouped): ?>
            <p class="muted">No homework assigned yet.</p>
        <?php else: ?>
            <?php foreach ($grouped as $subject => $items): ?>
                <section class="card">
                    <h2><?= h($subject) ?></h2>
                    <table class="changes-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Due date</th>
                                <th>Question</th>
                                <th>Answer</th>
                                <?php if ($currentUser): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $hw): ?>
                            <tr>
                                <td><?= h($hw['title']) ?></td>
                                <td><?= h($hw['due_date'] ?? '—') ?></td>
                                <td><?= nl2br(h(mb_strimwidth($hw['question'], 0, 160, '…', 'UTF-8'))) ?></td>
                                <td>
                                    <?php if ($hw['answer_path'] && (!$hw['due_date'] || $hw['due_date'] <= date('Y-m-d H:i:s'))): ?>
                                        <a href="<?= h($hw['answer_path']) ?>" target="_blank">Download</a>
                                    <?php elseif ($hw['answer_path']): ?>
                                        <span class="muted">Locked until due date</span>
                                    <?php else: ?>
                                        <span class="muted">Not available</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($currentUser): ?>
                                    <td>
                                        <?php if ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$hw['uploaded_by']): ?>
                                            <a href="<?= BASE_URL ?>/homework/edit?id=<?= (int)$hw['id'] ?>">Edit</a>
                                            <form method="post" action="<?= BASE_URL ?>/homework/delete" class="inline-form" style="display:inline;" onsubmit="return confirm('Delete this homework?');">
                                                <input type="hidden" name="id" value="<?= (int)$hw['id'] ?>">
                                                <button type="submit">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL ?>/homework?page=<?= $page - 1 ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL ?>/homework?page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/homework/new') {
    require_login();
    require_admin();

    $errors = [];
    $title = $question = '';
    $due_date = '';
    $subject_id = 0;

    $subjects = $pdo->query("
        SELECT id, name
        FROM subjects
        ORDER BY name ASC
    ")->fetchAll();

    if ($method === 'POST') {
        $title      = trim($_POST['title'] ?? '');
        $question   = trim($_POST['question'] ?? '');
        $due_date   = trim($_POST['due_date'] ?? '');
        $subject_id = (int)($_POST['subject_id'] ?? 0);

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if ($question === '') {
            $errors[] = "Question is required.";
        }
        if ($subject_id <= 0) {
            $errors[] = "Subject is required.";
        }

        $dt = null;
        if ($due_date !== '') {
            $dt = date('Y-m-d H:i:s', strtotime($due_date));
        }

        $answerPath = null;
        if (!$errors) {
            try {
                $answerPath = handle_file_upload('answer_file', 'homework_answer');
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO homework (title, question, subject_id, due_date, answer_path, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title,
                $question,
                $subject_id,
                $dt,
                $answerPath,
                $currentUser['id']
            ]);
            redirect('/homework');
        }
    }

    render_header('New Homework');
    ?>
    <section class="page">
        <h1>Create Homework</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
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
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                Subject
                <select name="subject_id" required>
                    <option value="">Select subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $subject_id === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= h($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Question
                <textarea name="question" rows="8" required><?= h($question) ?></textarea>
            </label>
            <label>
                Due date (optional)
                <input type="datetime-local" name="due_date" value="<?= h($due_date) ?>">
            </label>
            <label>
                Answer file (optional, PDF/DOC/IMG/ZIP, max 10MB)
                <input type="file" name="answer_file">
            </label>
            <button type="submit">Save</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/homework/edit') {
    require_login();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        render_header('Bad request');
        echo '<section class="page"><h1>Bad request</h1></section>';
        render_footer();
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM homework WHERE id = ?");
    $stmt->execute([$id]);
    $hw = $stmt->fetch();

    if (!$hw) {
        http_response_code(404);
        render_header('Homework not found');
        echo '<section class="page"><h1>Homework not found</h1></section>';
        render_footer();
        exit;
    }

    if (!$currentUser['is_admin'] && (int)$currentUser['id'] !== (int)$hw['uploaded_by']) {
        http_response_code(403);
        render_header('Forbidden');
        echo '<section class="page"><h1>403 Forbidden</h1></section>';
        render_footer();
        exit;
    }

    $subjects = $pdo->query("
        SELECT id, name
        FROM subjects
        ORDER BY name ASC
    ")->fetchAll();

    $errors = [];
    $title      = $hw['title'];
    $question   = $hw['question'];
    $subject_id = (int)($hw['subject_id'] ?? 0);
    $due_date   = $hw['due_date'] ? date('Y-m-d\TH:i', strtotime($hw['due_date'])) : '';
    $answerPath = $hw['answer_path'];

    if ($method === 'POST') {
        $title      = trim($_POST['title'] ?? '');
        $question   = trim($_POST['question'] ?? '');
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $due_date   = trim($_POST['due_date'] ?? '');

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if ($question === '') {
            $errors[] = "Question is required.";
        }
        if ($subject_id <= 0) {
            $errors[] = "Subject is required.";
        }

        $dt = null;
        if ($due_date !== '') {
            $dt = date('Y-m-d H:i:s', strtotime($due_date));
        }

        if (!$errors) {
            try {
                $newAnswer = handle_file_upload('answer_file', 'homework_answer');
                if ($newAnswer !== null) {
                    $answerPath = $newAnswer;
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE homework
                SET title = ?, question = ?, subject_id = ?, due_date = ?, answer_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title,
                $question,
                $subject_id,
                $dt,
                $answerPath,
                $id
            ]);
            redirect('/homework');
        }
    }

    render_header('Edit Homework');
    ?>
    <section class="page">
        <h1>Edit Homework</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <label>
                Title
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                Subject
                <select name="subject_id" required>
                    <option value="">Select subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $subject_id === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= h($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Question
                <textarea name="question" rows="8" required><?= h($question) ?></textarea>
            </label>
            <label>
                Due date (optional)
                <input type="datetime-local" name="due_date" value="<?= h($due_date) ?>">
            </label>
            <label>
                Replace answer file (optional)
                <input type="file" name="answer_file">
            </label>
            <button type="submit">Save changes</button>
        </form>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/homework/delete' && $method === 'POST') {
    require_login();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        redirect('/homework');
    }

    $stmt = $pdo->prepare("SELECT uploaded_by FROM homework WHERE id = ?");
    $stmt->execute([$id]);
    $hw = $stmt->fetch();

    if ($hw && ($currentUser['is_admin'] || (int)$currentUser['id'] === (int)$hw['uploaded_by'])) {
        $stmt = $pdo->prepare("DELETE FROM homework WHERE id = ?");
        $stmt->execute([$id]);
    }

    redirect('/homework');
}

/* ---- Reminders ---- */

if ($path === '/reminders') {
    require_login();

    $errors = [];
    $message = '';
    $due_date = '';

    if ($method === 'POST') {
        $message  = trim($_POST['message'] ?? '');
        $due_date = trim($_POST['due_date'] ?? '');

        if ($message === '') {
            $errors[] = "Message is required.";
        }
        if ($due_date === '') {
            $errors[] = "Due date is required.";
        }

        $dt = null;
        if ($due_date !== '') {
            $dt = date('Y-m-d H:i:s', strtotime($due_date));
        }

        if (!$errors && $dt) {
            $stmt = $pdo->prepare("
                INSERT INTO reminders (user_id, message, due_date)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$currentUser['id'], $message, $dt]);
            redirect('/reminders');
        }
    }

    $stmt = $pdo->prepare("
        SELECT * FROM reminders
        WHERE user_id = ?
        ORDER BY due_date ASC
    ");
    $stmt->execute([$currentUser['id']]);
    $rows = $stmt->fetchAll();

    render_header('Reminders');
    ?>
    <section class="page">
        <h1>Reminders</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <label>
                Message
                <input type="text" name="message" required value="<?= h($message) ?>">
            </label>
            <label>
                Due date
                <input type="datetime-local" name="due_date" required value="<?= h($due_date) ?>">
            </label>
            <button type="submit">Add reminder</button>
        </form>

        <h2>Your reminders</h2>
        <?php if ($rows): ?>
            <ul class="reminder-list reminder-list--full">
                <?php foreach ($rows as $r): ?>
                    <li>
                        <div class="reminder-msg"><?= h($r['message']) ?></div>
                        <div class="reminder-date"><?= h($r['due_date']) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="muted">No reminders yet.</p>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- Notes ---- */

if ($path === '/notes') {
    require_login();

    $errors = [];
    $title = $body = '';

    if ($method === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');

        if ($title === '') {
            $errors[] = "Title is required.";
        }
        if ($body === '') {
            $errors[] = "Body is required.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                INSERT INTO notes (user_id, title, body)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$currentUser['id'], $title, $body]);
            redirect('/notes');
        }
    }

    $stmt = $pdo->prepare("
        SELECT * FROM notes
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$currentUser['id']]);
    $notes = $stmt->fetchAll();

    render_header('Notes');
    ?>
    <section class="page">
        <h1>Notes</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <label>
                Title
                <input type="text" name="title" required value="<?= h($title) ?>">
            </label>
            <label>
                Body
                <textarea name="body" rows="6" required><?= h($body) ?></textarea>
            </label>
            <button type="submit">Add note</button>
        </form>

        <h2>Your notes</h2>
        <?php if ($notes): ?>
            <ul class="notes-list">
                <?php foreach ($notes as $n): ?>
                    <li>
                        <h3><?= h($n['title']) ?></h3>
                        <div class="muted"><?= h($n['created_at']) ?></div>
                        <p><?= nl2br(h($n['body'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="muted">No notes yet.</p>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- Public users list + per-user posts ---- */

if ($path === '/users') {
    // public users list with pagination
    $perPage = 20;
    $page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $perPage;

    $total = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    $totalPages = max(1, (int)ceil($total / $perPage));

    $sql = "
        SELECT id, username, is_admin, created_at
        FROM users
        ORDER BY username ASC
        LIMIT $perPage OFFSET $offset
    ";
    $users = $pdo->query($sql)->fetchAll();

    render_header('Users');
    ?>
    <section class="page">
        <h1>Users</h1>

        <?php if ($users): ?>
            <table class="changes-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Posts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= h($u['username']) ?></td>
                            <td><?= $u['is_admin'] ? 'Admin' : 'User' ?></td>
                            <td><?= h($u['created_at']) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/users/posts?id=<?= (int)$u['id'] ?>">View posts</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL ?>/users?page=<?= $page - 1 ?>">&laquo; Prev</a>
                    <?php endif; ?>
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL ?>/users?page=<?= $page + 1 ?>">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">No users yet.</p>
        <?php endif; ?>
    </section>
    <?php
    render_footer();
    exit;
}

if ($path === '/users/posts') {
    // public per-user posts view
    $userId = (int)($_GET['id'] ?? 0);
    if ($userId <= 0) {
        http_response_code(400);
        render_header('Bad request');
        echo '<section class="page"><h1>Bad request</h1></section>';
        render_footer();
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, is_admin FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        render_header('User not found');
        echo '<section class="page"><h1>User not found</h1></section>';
        render_footer();
        exit;
    }

    // pagination per section
    $perPageProg = 10;
    $perPageMan  = 10;
    $perPageHw   = 10;

    $pageProg = isset($_GET['page_prog']) && (int)$_GET['page_prog'] > 0 ? (int)$_GET['page_prog'] : 1;
    $pageMan  = isset($_GET['page_manual']) && (int)$_GET['page_manual'] > 0 ? (int)$_GET['page_manual'] : 1;
    $pageHw   = isset($_GET['page_hw']) && (int)$_GET['page_hw'] > 0 ? (int)$_GET['page_hw'] : 1;

    $offProg = ($pageProg - 1) * $perPageProg;
    $offMan  = ($pageMan - 1) * $perPageMan;
    $offHw   = ($pageHw - 1) * $perPageHw;

    // Lab programs by user
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_programs WHERE uploaded_by = ?");
    $stmt->execute([$userId]);
    $totalProg = (int)$stmt->fetch()['c'];
    $totPagesProg = max(1, (int)ceil($totalProg / $perPageProg));

    $sql = "
        SELECT lp.id, lp.title, lp.created_at,
               COALESCE(pl.name, lp.language) AS language_name
        FROM lab_programs lp
        LEFT JOIN programming_languages pl ON lp.language_id = pl.id
        WHERE lp.uploaded_by = ?
        ORDER BY lp.created_at DESC
        LIMIT $perPageProg OFFSET $offProg
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $userPrograms = $stmt->fetchAll();

    // Manuals by user
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM lab_manuals WHERE uploaded_by = ?");
    $stmt->execute([$userId]);
    $totalMan = (int)$stmt->fetch()['c'];
    $totPagesMan = max(1, (int)ceil($totalMan / $perPageMan));

    $sql = "
        SELECT id, title, pdf_path, created_at
        FROM lab_manuals
        WHERE uploaded_by = ?
        ORDER BY created_at DESC
        LIMIT $perPageMan OFFSET $offMan
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $userManuals = $stmt->fetchAll();

    // Homework by user
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM homework WHERE uploaded_by = ?");
    $stmt->execute([$userId]);
    $totalHw = (int)$stmt->fetch()['c'];
    $totPagesHw = max(1, (int)ceil($totalHw / $perPageHw));

    $sql = "
        SELECT h.id, h.title, h.due_date, h.created_at,
               COALESCE(s.name, 'Uncategorized') AS subject_name
        FROM homework h
        LEFT JOIN subjects s ON h.subject_id = s.id
        WHERE h.uploaded_by = ?
        ORDER BY h.created_at DESC
        LIMIT $perPageHw OFFSET $offHw
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $userHomework = $stmt->fetchAll();

    $base = BASE_URL . '/users/posts?id=' . $userId;

    render_header('Posts by ' . $user['username']);
    ?>
    <section class="page">
        <h1>Posts by <?= h($user['username']) ?> <?= $user['is_admin'] ? '(Admin)' : '' ?></h1>

        <section class="user-post-section">
            <h2>Lab Programs</h2>
            <?php if ($userPrograms): ?>
                <ul>
                    <?php foreach ($userPrograms as $p): ?>
                        <li>
                            <a href="<?= BASE_URL ?>/lab-programs/view?id=<?= (int)$p['id'] ?>">
                                <?= h($p['title']) ?>
                            </a>
                            <span class="muted">
                                (<?= h($p['language_name']) ?>,
                                <?= h($p['created_at']) ?>)
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($totPagesProg > 1): ?>
                    <nav class="pagination">
                        <?php if ($pageProg > 1): ?>
                            <a href="<?= $base ?>&page_prog=<?= $pageProg - 1 ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw ?>">&laquo; Prev</a>
                        <?php endif; ?>
                        <span>Page <?= $pageProg ?> of <?= $totPagesProg ?></span>
                        <?php if ($pageProg < $totPagesProg): ?>
                            <a href="<?= $base ?>&page_prog=<?= $pageProg + 1 ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted">No lab programs posted.</p>
            <?php endif; ?>
        </section>

        <section class="user-post-section">
            <h2>Lab Manuals</h2>
            <?php if ($userManuals): ?>
                <ul>
                    <?php foreach ($userManuals as $m): ?>
                        <li>
                            <a href="<?= h($m['pdf_path']) ?>" target="_blank">
                                <?= h($m['title']) ?>
                            </a>
                            <span class="muted">(<?= h($m['created_at']) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($totPagesMan > 1): ?>
                    <nav class="pagination">
                        <?php if ($pageMan > 1): ?>
                            <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan - 1 ?>&page_hw=<?= $pageHw ?>">&laquo; Prev</a>
                        <?php endif; ?>
                        <span>Page <?= $pageMan ?> of <?= $totPagesMan ?></span>
                        <?php if ($pageMan < $totPagesMan): ?>
                            <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan + 1 ?>&page_hw=<?= $pageHw ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted">No lab manuals posted.</p>
            <?php endif; ?>
        </section>

        <section class="user-post-section">
            <h2>Homework</h2>
            <?php if ($userHomework): ?>
                <ul>
                    <?php foreach ($userHomework as $h): ?>
                        <li>
                            <?= h($h['title']) ?>
                            <span class="muted">
                                (subject <?= h($h['subject_name']) ?>,
                                due <?= h($h['due_date'] ?? '—') ?>,
                                created <?= h($h['created_at']) ?>)
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($totPagesHw > 1): ?>
                    <nav class="pagination">
                        <?php if ($pageHw > 1): ?>
                            <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw - 1 ?>">&laquo; Prev</a>
                        <?php endif; ?>
                        <span>Page <?= $pageHw ?> of <?= $totPagesHw ?></span>
                        <?php if ($pageHw < $totPagesHw): ?>
                            <a href="<?= $base ?>&page_prog=<?= $pageProg ?>&page_manual=<?= $pageMan ?>&page_hw=<?= $pageHw + 1 ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted">No homework posted.</p>
            <?php endif; ?>
        </section>
    </section>
    <?php
    render_footer();
    exit;
}

/* ---- 404 ---- */

http_response_code(404);
render_header('Not found');
?>
<section class="page">
    <h1>404 – Page not found</h1>
</section>
<?php
render_footer();
