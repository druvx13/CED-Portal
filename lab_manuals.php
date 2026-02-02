<?php

/**
 * CED Portal - Lab Manuals Page
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

$page_title = 'Lab Manuals';
$page = 'manuals';

// Fetch lab manuals
$result = $db->query('SELECT lm.*, s.name as subject_name FROM '.$db->prefix.'lab_manuals AS lm LEFT JOIN '.$db->prefix.'subjects AS s ON lm.subject_id = s.id ORDER BY lm.created_at DESC');

$manuals = array();
while ($row = $db->fetch_assoc($result))
    $manuals[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl">Lab Manuals</p>
        <p class="postlink conr"><a href="lab_manuals.php?action=add">Upload New Manual</a></p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Lab Manuals</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($manuals)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No lab manuals found. <a href="lab_manuals.php?action=add">Upload your first manual</a></p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Manual Title</th>
                        <th class="tc2" scope="col">Subject</th>
                        <th class="tc3" scope="col">File</th>
                        <th class="tc4" scope="col">Date</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manuals as $manual): ?>
                    <tr>
                        <td class="tcl">
                            <?php echo ced_htmlspecialchars($manual['title']) ?>
                            <?php if (!empty($manual['description'])): ?>
                            <br /><span class="byuser"><?php echo ced_htmlspecialchars(substr($manual['description'], 0, 100)) ?><?php echo strlen($manual['description']) > 100 ? '...' : '' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="tc2"><?php echo $manual['subject_name'] ? ced_htmlspecialchars($manual['subject_name']) : 'N/A' ?></td>
                        <td class="tc3">
                            <?php if (!empty($manual['file_path'])): ?>
                            <a href="<?php echo ced_htmlspecialchars($manual['file_path']) ?>" target="_blank">Download PDF</a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </td>
                        <td class="tc4"><?php echo date('Y-m-d', $manual['created_at']) ?></td>
                        <td class="tcr">
                            <a href="lab_manuals.php?action=edit&amp;id=<?php echo $manual['id'] ?>">Edit</a> | 
                            <a href="lab_manuals.php?action=delete&amp;id=<?php echo $manual['id'] ?>" onclick="return confirm('Are you sure you want to delete this manual?')">Delete</a>
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

