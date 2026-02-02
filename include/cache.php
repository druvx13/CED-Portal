<?php

/**
 * CED Portal Cache Functions
 * Adapted from FluxBB
 */

if (!defined('CED'))
    exit;

define('CED_CACHE_FUNCTIONS_LOADED', 1);

// Generate configuration cache
function generate_config_cache()
{
    global $db;
    
    // Get config from database
    $result = $db->query('SELECT * FROM '.$db->prefix.'config');
    
    $output = array();
    while ($cur_config_item = $db->fetch_row($result))
        $output[$cur_config_item[0]] = $cur_config_item[1];
    
    // Output to cache file
    $fh = @fopen(CED_CACHE_DIR.'cache_config.php', 'wb');
    if (!$fh)
        error('Unable to write configuration cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'.', __FILE__, __LINE__);
    
    fwrite($fh, '<?php'."\n\n".'define(\'CED_CONFIG_LOADED\', 1);'."\n\n".'$ced_config = '.var_export($output, true).';'."\n\n".'?>');
    
    fclose($fh);
}

// Generate ranks cache
function generate_ranks_cache()
{
    global $db;
    
    // Get ranks from database
    $result = $db->query('SELECT * FROM '.$db->prefix.'ranks ORDER BY min_posts');
    
    $output = array();
    while ($cur_rank = $db->fetch_assoc($result))
        $output[] = $cur_rank;
    
    // Output to cache file
    $fh = @fopen(CED_CACHE_DIR.'cache_ranks.php', 'wb');
    if (!$fh)
        error('Unable to write ranks cache file to cache directory. Please make sure PHP has write access to the directory \'cache\'.', __FILE__, __LINE__);
    
    fwrite($fh, '<?php'."\n\n".'define(\'CED_RANKS_LOADED\', 1);'."\n\n".'$ced_ranks = '.var_export($output, true).';'."\n\n".'?>');
    
    fclose($fh);
}

// Clear all cache files
function clear_cache()
{
    $cache_files = array('cache_config.php', 'cache_ranks.php');
    
    foreach ($cache_files as $cache_file)
    {
        $file_path = CED_CACHE_DIR.$cache_file;
        if (file_exists($file_path))
            @unlink($file_path);
    }
}

