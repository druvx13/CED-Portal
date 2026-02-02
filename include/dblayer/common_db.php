<?php

/**
 * CED Portal Database Abstraction Layer
 * Common loader - Adapted from FluxBB
 */

if (!defined('CED'))
    exit;

// Load the appropriate DB layer class
switch ($db_type)
{
    case 'mysqli':
        require_once CED_ROOT.'include/dblayer/mysqli.php';
        break;
    
    case 'pgsql':
        require_once CED_ROOT.'include/dblayer/pgsql.php';
        break;
    
    case 'sqlite3':
        require_once CED_ROOT.'include/dblayer/sqlite3.php';
        break;
    
    default:
        error('\''.$db_type.'\' is not a valid database type. Please check settings in config.php.', __FILE__, __LINE__);
        break;
}

// Create the database adapter object
$db = new DBLayer($db_host, $db_username, $db_password, $db_name, $db_prefix, $p_connect);

