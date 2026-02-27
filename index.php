<?php

/**
 * CED Portal - Index/Home Page
 * Adapted from FluxBB architecture
 */

define('CED_ROOT', './');
require CED_ROOT.'include/common.php';

$page_title = 'Home';
$page = 'home';

require CED_ROOT.'header.php';

?>

<div class="blockform">
    <h2><span>Welcome to CED Portal</span></h2>
    <div class="box">
        <div class="inbox">
            <div class="inform">
                <div class="forminfo">
                    <h3>Computer Engineering Department Portal</h3>
                    <p>Manage your lab programs, manuals, homework, notes, and reminders all in one place.</p>
                </div>
            </div>
            
            <?php if (!$ced_user['is_guest']): ?>
            <div class="inform">
                <fieldset>
                    <legend>Quick Links</legend>
                    <div class="infldset">
                        <ul class="bblinks">
                            <li><a href="lab_programs.php">View Lab Programs</a></li>
                            <li><a href="lab_manuals.php">View Lab Manuals</a></li>
                            <li><a href="homework.php">View Homework</a></li>
                            <li><a href="notes.php">View Notes</a></li>
                            <li><a href="reminders.php">View Reminders</a></li>
                            <?php if ($ced_user['is_admin']): ?>
                            <li><a href="admin_index.php">Administration Panel</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </fieldset>
            </div>
            <?php else: ?>
            <div class="inform">
                <div class="forminfo">
                    <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to access the portal features.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php

require CED_ROOT.'footer.php';

