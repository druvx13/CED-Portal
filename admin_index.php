<?php

/**
 * CED Portal - Admin Index
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

$page_title = 'Administration';
$page = 'admin';

require CED_ROOT.'header.php';

?>

<div class="blockform">
    <h2><span>Administration</span></h2>
    <div class="box">
        <div class="inbox">
            <p>Welcome to the CED Portal Administration Panel</p>
        </div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Management</span></h2>
    <div class="box">
        <div class="inbox">
            <table cellspacing="0">
                <tbody>
                    <tr>
                        <td><a href="admin_users.php">Manage Users</a></td>
                        <td>Add, edit, or remove user accounts</td>
                    </tr>
                    <tr>
                        <td><a href="admin_subjects.php">Manage Subjects</a></td>
                        <td>Add, edit, or remove subjects</td>
                    </tr>
                    <tr>
                        <td><a href="admin_languages.php">Manage Languages</a></td>
                        <td>Add, edit, or remove programming languages</td>
                    </tr>
                    <tr>
                        <td><a href="admin_options.php">Options</a></td>
                        <td>Configure portal settings</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Statistics</span></h2>
    <div class="box">
        <div class="inbox">
            <?php
            // Get statistics
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'users WHERE id > 1');
            $num_users = $db->result($result);
            
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'lab_programs');
            $num_programs = $db->result($result);
            
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'lab_manuals');
            $num_manuals = $db->result($result);
            
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'homework');
            $num_homework = $db->result($result);
            
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'notes');
            $num_notes = $db->result($result);
            
            $result = $db->query('SELECT COUNT(*) FROM '.$db->prefix.'reminders');
            $num_reminders = $db->result($result);
            ?>
            <table cellspacing="0">
                <tbody>
                    <tr>
                        <td><strong>Total Users:</strong></td>
                        <td><?php echo $num_users ?></td>
                    </tr>
                    <tr>
                        <td><strong>Lab Programs:</strong></td>
                        <td><?php echo $num_programs ?></td>
                    </tr>
                    <tr>
                        <td><strong>Lab Manuals:</strong></td>
                        <td><?php echo $num_manuals ?></td>
                    </tr>
                    <tr>
                        <td><strong>Homework Assignments:</strong></td>
                        <td><?php echo $num_homework ?></td>
                    </tr>
                    <tr>
                        <td><strong>Notes:</strong></td>
                        <td><?php echo $num_notes ?></td>
                    </tr>
                    <tr>
                        <td><strong>Reminders:</strong></td>
                        <td><?php echo $num_reminders ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

require CED_ROOT.'footer.php';

