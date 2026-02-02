<?php

/**
 * CED Portal - Admin Subjects
 * Adapted from FluxBB architecture
 */

define('CED_ROOT', './');
define('CED_ADMIN_CONSOLE', 1);
require CED_ROOT.'include/common.php';

// Check if user is admin
if ($ced_user['is_guest'] || !$ced_user['is_admin'])
{
    header('HTTP/1.1 403 Forbidden');
    exit('You do not have permission to access this page.');
}

$page_title = 'Manage Subjects';
$page = 'admin';

// Fetch all subjects
$result = $db->query('SELECT * FROM '.$db->prefix.'subjects ORDER BY name');

$subjects = array();
while ($row = $db->fetch_assoc($result))
    $subjects[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl"><a href="admin_index.php">Admin</a> &raquo; Manage Subjects</p>
        <p class="postlink conr"><a href="admin_subjects.php?action=add">Add New Subject</a></p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Subjects</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($subjects)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No subjects found. <a href="admin_subjects.php?action=add">Add your first subject</a></p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Subject Name</th>
                        <th class="tc2" scope="col">Code</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td class="tcl"><?php echo ced_htmlspecialchars($subject['name']) ?></td>
                        <td class="tc2"><?php echo ced_htmlspecialchars($subject['code']) ?></td>
                        <td class="tcr">
                            <a href="admin_subjects.php?action=edit&amp;id=<?php echo $subject['id'] ?>">Edit</a> | 
                            <a href="admin_subjects.php?action=delete&amp;id=<?php echo $subject['id'] ?>" onclick="return confirm('Are you sure you want to delete this subject?')">Delete</a>
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

