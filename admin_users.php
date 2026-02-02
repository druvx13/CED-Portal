<?php

/**
 * CED Portal - Admin Users
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

$page_title = 'Manage Users';
$page = 'admin';

// Fetch all users
$result = $db->query('SELECT u.*, g.g_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id = g.g_id WHERE u.id > 1 ORDER BY u.username');

$users = array();
while ($row = $db->fetch_assoc($result))
    $users[] = $row;

require CED_ROOT.'header.php';

?>

<div class="linkst">
    <div class="inbox">
        <p class="pagelink conl"><a href="admin_index.php">Admin</a> &raquo; Manage Users</p>
        <div class="clearer"></div>
    </div>
</div>

<div class="blocktable">
    <h2><span>Users</span></h2>
    <div class="box">
        <div class="inbox">
            <?php if (empty($users)): ?>
            <div class="inform">
                <div class="forminfo">
                    <p>No users found.</p>
                </div>
            </div>
            <?php else: ?>
            <table cellspacing="0">
                <thead>
                    <tr>
                        <th class="tcl" scope="col">Username</th>
                        <th class="tc2" scope="col">Email</th>
                        <th class="tc3" scope="col">Group</th>
                        <th class="tc4" scope="col">Registered</th>
                        <th class="tcr" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="tcl"><?php echo ced_htmlspecialchars($user['username']) ?></td>
                        <td class="tc2"><?php echo ced_htmlspecialchars($user['email']) ?></td>
                        <td class="tc3"><?php echo $user['g_title'] ? ced_htmlspecialchars($user['g_title']) : 'N/A' ?></td>
                        <td class="tc4"><?php echo date('Y-m-d', $user['registered']) ?></td>
                        <td class="tcr">
                            <a href="admin_users.php?action=edit&amp;id=<?php echo $user['id'] ?>">Edit</a>
                            <?php if ($user['id'] != $ced_user['id']): ?>
                             | <a href="admin_users.php?action=delete&amp;id=<?php echo $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            <?php endif; ?>
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

