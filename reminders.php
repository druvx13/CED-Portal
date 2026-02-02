<?php

/**
 * CED Portal - Reminders Page
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

$page_title = 'Reminders';
$page = 'reminders';

// Fetch reminders for current user
$result = $db->query('SELECT * FROM '.$db->prefix.'reminders WHERE user_id='.$ced_user['id'].' ORDER BY reminder_date ASC, created_at DESC');

$reminders = array();
while ($row = $db->fetch_assoc($result))
    $reminders[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl">My Reminders</p>
        <p class="postlink conr"><a href="reminders.php?action=add">Add New Reminder</a></p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>My Reminders</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($reminders)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No reminders found. <a href="reminders.php?action=add">Create your first reminder</a></p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Reminder Title</th>
                        <th class="tc2" scope="col">Description</th>
                        <th class="tc3" scope="col">Reminder Date</th>
                        <th class="tc4" scope="col">Status</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reminders as $reminder): ?>
                    <?php $is_past = $reminder['reminder_date'] && $reminder['reminder_date'] < time(); ?>
                    <tr<?php echo $is_past ? ' class="iposted"' : '' ?>>
                        <td class="tcl">
                            <a href="reminders.php?id=<?php echo $reminder['id'] ?>">
                                <?php echo ced_htmlspecialchars($reminder['title']) ?>
                            </a>
                        </td>
                        <td class="tc2">
                            <?php echo ced_htmlspecialchars(substr($reminder['description'], 0, 100)) ?><?php echo strlen($reminder['description']) > 100 ? '...' : '' ?>
                        </td>
                        <td class="tc3">
                            <?php echo $reminder['reminder_date'] ? date('Y-m-d H:i', $reminder['reminder_date']) : 'No date set' ?>
                        </td>
                        <td class="tc4">
                            <?php if ($is_past): ?>
                            <span style="color: red;">Past</span>
                            <?php else: ?>
                            <span style="color: green;">Upcoming</span>
                            <?php endif; ?>
                        </td>
                        <td class="tcr">
                            <a href="reminders.php?action=edit&amp;id=<?php echo $reminder['id'] ?>">Edit</a> | 
                            <a href="reminders.php?action=delete&amp;id=<?php echo $reminder['id'] ?>" onclick="return confirm('Are you sure you want to delete this reminder?')">Delete</a>
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

