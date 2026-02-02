<?php

/**
 * CED Portal - Notes Page
 * Adapted from FluxBB architecture
 */

define('CED_ROOT', './');
require CED_ROOT.'include/common.php';

// Check if user is logged in
if ($ced_user['is_guest'])
{
    header('Location: login.php');
    exit;
}

$page_title = 'Notes';
$page = 'notes';

// Fetch notes for current user
$result = $db->query('SELECT * FROM '.$db->prefix.'notes WHERE user_id='.$ced_user['id'].' ORDER BY created_at DESC');

$notes = array();
while ($row = $db->fetch_assoc($result))
    $notes[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl">My Notes</p>
        <p class="postlink conr"><a href="notes.php?action=add">Add New Note</a></p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>My Notes</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($notes)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No notes found. <a href="notes.php?action=add">Create your first note</a></p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Note Title</th>
                        <th class="tc2" scope="col">Content</th>
                        <th class="tc3" scope="col">Date</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                    <tr>
                        <td class="tcl">
                            <a href="notes.php?id=<?php echo $note['id'] ?>">
                                <?php echo ced_htmlspecialchars($note['title']) ?>
                            </a>
                        </td>
                        <td class="tc2">
                            <?php echo ced_htmlspecialchars(substr($note['content'], 0, 100)) ?><?php echo strlen($note['content']) > 100 ? '...' : '' ?>
                        </td>
                        <td class="tc3"><?php echo date('Y-m-d H:i', $note['created_at']) ?></td>
                        <td class="tcr">
                            <a href="notes.php?action=edit&amp;id=<?php echo $note['id'] ?>">Edit</a> | 
                            <a href="notes.php?action=delete&amp;id=<?php echo $note['id'] ?>" onclick="return confirm('Are you sure you want to delete this note?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php

require CED_ROOT.'footer.php';

