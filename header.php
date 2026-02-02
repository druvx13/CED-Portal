<?php

/**
 * CED Portal Header
 * Adapted from FluxBB
 */

if (!defined('CED'))
    exit;

// Send headers
header('Content-type: text/html; charset=utf-8');
header('X-Frame-Options: deny');

// Prevent caching
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Load the template
$tpl_file = defined('CED_ADMIN_CONSOLE') ? 'admin.tpl' : 'main.tpl';

if (file_exists(CED_ROOT.'style/'.$ced_user['style'].'/'.$tpl_file))
    $tpl_file = CED_ROOT.'style/'.$ced_user['style'].'/'.$tpl_file;
else
    $tpl_file = CED_ROOT.'include/template/'.$tpl_file;

$tpl_main = file_get_contents($tpl_file);

// START SUBST - <ced_language>
$tpl_main = str_replace('<ced_language>', 'en', $tpl_main);
// END SUBST

// START SUBST - <ced_head>
ob_start();

if (!defined('CED_ALLOW_INDEX'))
    echo '<meta name="ROBOTS" content="NOINDEX, FOLLOW" />'."\n";

?>
<title><?php echo isset($page_title) ? ced_htmlspecialchars($page_title).' - CED Portal' : 'CED Portal' ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo CED_BASE_URL ?>/style/<?php echo $ced_user['style'] ?>.css" />
<script type="text/javascript" src="<?php echo CED_BASE_URL ?>/js/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="<?php echo CED_BASE_URL ?>/js/common.js"></script>
<?php

$tpl_temp = ob_get_contents();
$tpl_main = str_replace('<ced_head>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <ced_head>

// START SUBST - <ced_page>
$tpl_main = str_replace('<ced_page>', isset($page) ? $page : 'home', $tpl_main);
// END SUBST

// START SUBST - <ced_title>
ob_start();
?>
<h1><a href="<?php echo CED_BASE_URL ?>/">CED Portal</a></h1>
<?php

$tpl_temp = ob_get_contents();
$tpl_main = str_replace('<ced_title>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <ced_title>

// START SUBST - <ced_desc>
$tpl_main = str_replace('<ced_desc>', isset($ced_config['o_board_desc']) ? '<p id="brddesc">'.$ced_config['o_board_desc'].'</p>' : '', $tpl_main);
// END SUBST

// START SUBST - <ced_navlinks>
ob_start();
?>
<div id="brdmenu" class="inbox">
    <ul>
        <li<?php echo !isset($page) || $page == 'home' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/">Home</a></li>
        <?php if (!$ced_user['is_guest']): ?>
        <li<?php echo isset($page) && $page == 'lab-programs' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/lab_programs.php">Lab Programs</a></li>
        <li<?php echo isset($page) && $page == 'manuals' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/lab_manuals.php">Lab Manuals</a></li>
        <li<?php echo isset($page) && $page == 'homework' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/homework.php">Homework</a></li>
        <li<?php echo isset($page) && $page == 'notes' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/notes.php">Notes</a></li>
        <li<?php echo isset($page) && $page == 'reminders' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/reminders.php">Reminders</a></li>
        <?php if ($ced_user['is_admin']): ?>
        <li<?php echo isset($page) && $page == 'admin' ? ' class="isactive"' : '' ?>><a href="<?php echo CED_BASE_URL ?>/admin_index.php">Admin</a></li>
        <?php endif; ?>
        <?php endif; ?>
    </ul>
</div>
<?php

$tpl_temp = ob_get_contents();
$tpl_main = str_replace('<ced_navlinks>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <ced_navlinks>

// START SUBST - <ced_status>
ob_start();
?>
<div id="brdwelcome" class="inbox">
    <?php if ($ced_user['is_guest']): ?>
    <p class="conl">Not logged in. <a href="<?php echo CED_BASE_URL ?>/login.php">Login</a> or <a href="<?php echo CED_BASE_URL ?>/register.php">Register</a></p>
    <?php else: ?>
    <p class="conl">Logged in as <strong><?php echo ced_htmlspecialchars($ced_user['username']) ?></strong> (<a href="<?php echo CED_BASE_URL ?>/profile.php?id=<?php echo $ced_user['id'] ?>">Profile</a> | <a href="<?php echo CED_BASE_URL ?>/login.php?action=out">Logout</a>)</p>
    <?php endif; ?>
    <div class="clearer"></div>
</div>
<?php

$tpl_temp = ob_get_contents();
$tpl_main = str_replace('<ced_status>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <ced_status>

// START SUBST - <ced_announcement>
$tpl_main = str_replace('<ced_announcement>', isset($announcement) ? $announcement : '', $tpl_main);
// END SUBST

// START SUBST - <ced_main>
ob_start();

define('CED_HEADER', 1);

