<?php

/**
 * CED Portal Footer
 * Adapted from FluxBB
 */

if (!defined('CED'))
    exit;

// END SUBST - <ced_main>
$tpl_temp = ob_get_contents();
$tpl_main = str_replace('<ced_main>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <ced_main>

// START SUBST - <ced_footer>
ob_start();

?>
<div id="brdfooter" class="block">
    <div class="box">
        <div class="inbox">
            <p class="conl">Powered by <strong>CED Portal</strong> <?php echo CED_VERSION ?></p>
            <p class="conr">Page generated in <?php echo sprintf('%.3f', microtime(true) - $ced_start) ?> seconds with <?php echo $db->get_num_queries() ?> queries</p>
            <div class="clearer"></div>
        </div>
    </div>
</div>
<?php

$tpl_temp = ob_get_contents();
$tpl_main = str_replace('<ced_footer>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <ced_footer>

// START SUBST - <ced_javascript>
$tpl_main = str_replace('<!-- ced_javascript -->', '', $tpl_main);
// END SUBST

// Close the database connection
$db->close();

// Output the page
echo $tpl_main;

