<?php

/**
 * CED Portal - Lab Programs Page
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

$page_title = 'Lab Programs';
$page = 'lab-programs';

// Fetch lab programs
$result = $db->query('SELECT lp.*, s.name as subject_name, pl.name as language_name FROM '.$db->prefix.'lab_programs AS lp LEFT JOIN '.$db->prefix.'subjects AS s ON lp.subject_id = s.id LEFT JOIN '.$db->prefix.'programming_languages AS pl ON lp.language_id = pl.id ORDER BY lp.created_at DESC');

$programs = array();
while ($row = $db->fetch_assoc($result))
    $programs[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl">Lab Programs</p>
        <p class="postlink conr"><a href="lab_programs.php?action=add">Add New Program</a></p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Lab Programs</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($programs)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No lab programs found. <a href="lab_programs.php?action=add">Add your first program</a></p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Program Title</th>
                        <th class="tc2" scope="col">Subject</th>
                        <th class="tc3" scope="col">Language</th>
                        <th class="tc4" scope="col">Date</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $program): ?>
                    <tr>
                        <td class="tcl">
                            <a href="lab_programs.php?id=<?php echo $program['id'] ?>">
                                <?php echo ced_htmlspecialchars($program['title']) ?>
                            </a>
                            <?php if (!empty($program['description'])): ?>
                            <br /><span class="byuser"><?php echo ced_htmlspecialchars(substr($program['description'], 0, 100)) ?><?php echo strlen($program['description']) > 100 ? '...' : '' ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="tc2"><?php echo $program['subject_name'] ? ced_htmlspecialchars($program['subject_name']) : 'N/A' ?></td>
                        <td class="tc3"><?php echo $program['language_name'] ? ced_htmlspecialchars($program['language_name']) : 'N/A' ?></td>
                        <td class="tc4"><?php echo date('Y-m-d', $program['created_at']) ?></td>
                        <td class="tcr">
                            <a href="lab_programs.php?action=edit&amp;id=<?php echo $program['id'] ?>">Edit</a> | 
                            <a href="lab_programs.php?action=delete&amp;id=<?php echo $program['id'] ?>" onclick="return confirm('Are you sure you want to delete this program?')">Delete</a>
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

