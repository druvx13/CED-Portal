<?php

/**
 * CED Portal - Homework Page
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

$page_title = 'Homework';
$page = 'homework';

// Fetch homework
$result = $db->query('SELECT h.*, s.name as subject_name FROM '.$db->prefix.'homework AS h LEFT JOIN '.$db->prefix.'subjects AS s ON h.subject_id = s.id ORDER BY h.due_date DESC, h.created_at DESC');

$homework = array();
while ($row = $db->fetch_assoc($result))
    $homework[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl">Homework</p>
        <p class="postlink conr"><a href="homework.php?action=add">Add New Homework</a></p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Homework Assignments</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($homework)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No homework found. <a href="homework.php?action=add">Add your first assignment</a></p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Assignment Title</th>
                        <th class="tc2" scope="col">Subject</th>
                        <th class="tc3" scope="col">Due Date</th>
                        <th class="tc4" scope="col">Status</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($homework as $hw): ?>
                    <?php $is_overdue = $hw['due_date'] && $hw['due_date'] < time(); ?>
                    <tr<?php echo $is_overdue ? ' class="iposted"' : '' ?>>
                        <td class="tcl">
                            <a href="homework.php?id=<?php echo $hw['id'] ?>">
                                <?php echo ced_htmlspecialchars($hw['title']) ?>
                            </a>
                            <?php if (!empty($hw['question'])): ?>
                            <br /><span class="byuser"><?php echo ced_htmlspecialchars(substr($hw['question'], 0, 100)) ?><?php echo strlen($hw['question']) > 100 ? '...' : '' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="tc2"><?php echo $hw['subject_name'] ? ced_htmlspecialchars($hw['subject_name']) : 'N/A' ?></td>
                        <td class="tc3">
                            <?php echo $hw['due_date'] ? date('Y-m-d', $hw['due_date']) : 'No due date' ?>
                            <?php if ($is_overdue): ?>
                            <br /><span style="color: red;">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td class="tc4">
                            <?php echo $hw['completed'] ? '<span style="color: green;">Completed</span>' : '<span style="color: orange;">Pending</span>' ?>
                        </td>
                        <td class="tcr">
                            <a href="homework.php?action=edit&amp;id=<?php echo $hw['id'] ?>">Edit</a> | 
                            <a href="homework.php?action=delete&amp;id=<?php echo $hw['id'] ?>" onclick="return confirm('Are you sure you want to delete this homework?')">Delete</a>
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

