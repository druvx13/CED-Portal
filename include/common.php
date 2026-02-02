<?php

/**
 * CED Portal Common Bootstrap
 * Adapted from FluxBB architecture
 * This file initializes the application and should be included by all page scripts
 */

if (!defined('CED_ROOT'))
    exit('The constant CED_ROOT must be defined and point to a valid CED Portal installation root directory.');

// Error reporting
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Force POSIX locale
setlocale(LC_CTYPE, 'C');

// UTF-8 settings
mb_language('uni');
mb_internal_encoding('UTF-8');
mb_substitute_character(0xFFFD);

// Record start time for performance tracking
$ced_start = empty($_SERVER['REQUEST_TIME_FLOAT']) ? microtime(true) : (float) $_SERVER['REQUEST_TIME_FLOAT'];

// Define version
define('CED_VERSION', '2.0.0');
define('CED_DB_REVISION', 1);

// Load configuration
if (file_exists(CED_ROOT.'include/config.php'))
    require CED_ROOT.'include/config.php';

// If CED isn't defined, config.php is missing
if (!defined('CED'))
{
    header('Location: install.php');
    exit;
}

// Load functions
require CED_ROOT.'include/functions.php';

// Strip bad UTF-8 characters
ced_remove_bad_characters();

// Set cookie name
if (empty($cookie_name))
    $cookie_name = 'ced_cookie';

// Cache directory
if (!defined('CED_CACHE_DIR'))
    define('CED_CACHE_DIR', CED_ROOT.'cache/');

// User group constants
define('CED_ADMIN', 1);
define('CED_STUDENT', 2);
define('CED_GUEST', 3);

// Load DB abstraction layer
require CED_ROOT.'include/dblayer/common_db.php';

// Load cached config
if (file_exists(CED_CACHE_DIR.'cache_config.php'))
    include CED_CACHE_DIR.'cache_config.php';

if (!defined('CED_CONFIG_LOADED'))
{
    if (!defined('CED_CACHE_FUNCTIONS_LOADED'))
        require CED_ROOT.'include/cache.php';
    
    generate_config_cache();
    require CED_CACHE_DIR.'cache_config.php';
}

// Enable output buffering
if (!defined('CED_DISABLE_BUFFERING'))
{
    if (isset($ced_config['o_gzip']) && $ced_config['o_gzip'] && extension_loaded('zlib'))
        ob_start('ob_gzhandler');
    else
        ob_start();
}

// Check/update cookie and fetch user info
$ced_user = array();
check_cookie($ced_user);

// Load language file
if (file_exists(CED_ROOT.'lang/'.$ced_user['language'].'/common.php'))
    include CED_ROOT.'lang/'.$ced_user['language'].'/common.php';
else
    error('There is no valid language pack \''.ced_htmlspecialchars($ced_user['language']).'\' installed.');

// Check maintenance mode
if (isset($ced_config['o_maintenance']) && $ced_config['o_maintenance'] && $ced_user['g_id'] > CED_ADMIN && !defined('CED_TURN_OFF_MAINT'))
    maintenance_message();

