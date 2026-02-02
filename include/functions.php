<?php

/**
 * CED Portal Functions Library
 * Adapted from FluxBB
 */

// Remove bad UTF-8 characters
function ced_remove_bad_characters()
{
    $bad_utf8_chars = array(
        "\xc0\x80" => "\x00",
        "\xc0\xaf" => '/',
        "\xc1\x9c" => '<',
        "\xc1\x9d" => '>',
        "\xc1\xa5" => '%',
        "\xc1\xa7" => '\'',
        "\xc1\xa2" => '"',
    );

    $_GET = remove_bad_characters($_GET, $bad_utf8_chars);
    $_POST = remove_bad_characters($_POST, $bad_utf8_chars);
    $_COOKIE = remove_bad_characters($_COOKIE, $bad_utf8_chars);
    $_REQUEST = remove_bad_characters($_REQUEST, $bad_utf8_chars);
}

function remove_bad_characters($array, array $bad_utf8_chars)
{
    foreach ($array as $key => $value)
    {
        $array[$key] = is_array($value) ? remove_bad_characters($value, $bad_utf8_chars) : str_replace(array_keys($bad_utf8_chars), array_values($bad_utf8_chars), $value);
    }
    return $array;
}

// Check cookie and authenticate user
function check_cookie(array &$ced_user)
{
    global $db, $ced_config, $cookie_name, $cookie_seed;
    
    $now = time();
    
    // Check for cookie
    if (isset($_COOKIE[$cookie_name]) && preg_match('%^(\d+)\|([0-9a-fA-F]+)\|(\d+)\|([0-9a-fA-F]+)$%', $_COOKIE[$cookie_name], $matches))
    {
        $cookie = array(
            'user_id' => intval($matches[1]),
            'password_hash' => $matches[2],
            'expiration_time' => intval($matches[3]),
            'cookie_hash' => $matches[4],
        );
        
        // Validate cookie
        if ($cookie['user_id'] > 0 && $cookie['expiration_time'] > $now)
        {
            if (hash_equals(ced_hmac($cookie['user_id'].'|'.$cookie['expiration_time'], $cookie_seed.'_cookie_hash'), $cookie['cookie_hash']))
            {
                // Fetch user from database
                $result = $db->query('SELECT u.*, g.g_id, g.g_title, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.id='.$cookie['user_id']);
                
                if ($result && $row = $db->fetch_assoc($result))
                {
                    // Verify password hash
                    if (hash_equals(ced_hmac($row['password'], $cookie_seed.'_password_hash'), $cookie['password_hash']))
                    {
                        $ced_user = $row;
                        
                        // Refresh cookie
                        $expire = $now + (isset($ced_config['o_timeout_visit']) ? $ced_config['o_timeout_visit'] : 1800);
                        ced_setcookie($ced_user['id'], $ced_user['password'], $expire);
                        
                        // Set defaults
                        if (!isset($ced_user['language']) || !file_exists(CED_ROOT.'lang/'.$ced_user['language']))
                            $ced_user['language'] = 'English';
                        
                        $ced_user['is_guest'] = false;
                        $ced_user['is_admin'] = ($ced_user['g_id'] == CED_ADMIN);
                        
                        return;
                    }
                }
            }
        }
        
        // Invalid cookie - delete it
        ced_setcookie($cookie_name, '', 1);
    }
    
    // Set default guest user
    set_default_user($ced_user);
}

// Set default guest user
function set_default_user(&$ced_user)
{
    global $db, $ced_config;
    
    $ced_user = array(
        'id' => 1,
        'username' => 'Guest',
        'password' => '',
        'email' => 'guest@example.com',
        'group_id' => CED_GUEST,
        'g_id' => CED_GUEST,
        'g_title' => 'Guest',
        'g_user_title' => 'Guest',
        'language' => isset($ced_config['o_default_lang']) ? $ced_config['o_default_lang'] : 'English',
        'style' => isset($ced_config['o_default_style']) ? $ced_config['o_default_style'] : 'Air',
        'is_guest' => true,
        'is_admin' => false,
    );
}

// Authenticate user
function authenticate_user($username, $password)
{
    global $db, $ced_user;
    
    $result = $db->query('SELECT u.*, g.g_id, g.g_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.username=\''.$db->escape($username).'\'');
    
    if ($result && $user = $db->fetch_assoc($result))
    {
        if (password_verify($password, $user['password']))
        {
            $ced_user = $user;
            $ced_user['is_guest'] = false;
            $ced_user['is_admin'] = ($user['g_id'] == CED_ADMIN);
            return true;
        }
    }
    
    return false;
}

// Set cookie
function ced_setcookie($user_id, $password_hash, $expire)
{
    global $cookie_name, $cookie_domain, $cookie_path, $cookie_secure, $cookie_seed;
    
    if (!isset($cookie_name))
        return;
    
    $now = time();
    $cookie_hash = ced_hmac($user_id.'|'.$expire, $cookie_seed.'_cookie_hash');
    $password_cookie_hash = ced_hmac($password_hash, $cookie_seed.'_password_hash');
    
    $cookie_value = $user_id.'|'.$password_cookie_hash.'|'.$expire.'|'.$cookie_hash;
    
    setcookie($cookie_name, $cookie_value, $expire, $cookie_path, $cookie_domain, $cookie_secure, true);
}

// HMAC function
function ced_hmac($data, $key)
{
    return hash_hmac('sha256', $data, $key);
}

// HTML special characters
function ced_htmlspecialchars($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Linebreaks to HTML
function ced_linebreaks($text)
{
    return nl2br(ced_htmlspecialchars($text));
}

// Error handler
function error($message, $file = '', $line = '', $db_error = '')
{
    global $ced_user;
    
    // Set content type
    header('Content-type: text/html; charset=utf-8');
    
    // Display error
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>CED Portal Error</title>';
    echo '<style>body{font:14px/1.5 Arial,sans-serif;background:#f5f5f5;padding:20px}';
    echo '.error{background:#fff;border:1px solid #ddd;border-radius:5px;padding:20px;max-width:600px;margin:0 auto}';
    echo 'h1{color:#d00;margin:0 0 10px}p{margin:5px 0}</style></head><body>';
    echo '<div class="error"><h1>An error occurred</h1>';
    echo '<p><strong>Error:</strong> '.ced_htmlspecialchars($message).'</p>';
    
    if ($file)
        echo '<p><strong>File:</strong> '.ced_htmlspecialchars($file).'</p>';
    if ($line)
        echo '<p><strong>Line:</strong> '.$line.'</p>';
    if ($db_error)
        echo '<p><strong>Database error:</strong> '.ced_htmlspecialchars($db_error).'</p>';
    
    echo '</div></body></html>';
    exit;
}

// Maintenance message
function maintenance_message()
{
    global $ced_config, $ced_user;
    
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Content-type: text/html; charset=utf-8');
    
    $message = isset($ced_config['o_maintenance_message']) ? $ced_config['o_maintenance_message'] : 'The site is currently undergoing maintenance. Please try again later.';
    
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Maintenance</title>';
    echo '<style>body{font:16px/1.5 Arial,sans-serif;background:#f5f5f5;padding:40px;text-align:center}';
    echo '.msg{background:#fff;border:2px solid #f90;border-radius:5px;padding:30px;max-width:600px;margin:0 auto;display:inline-block}';
    echo 'h1{color:#f90;margin:0 0 15px}</style></head><body>';
    echo '<div class="msg"><h1>Maintenance Mode</h1><p>'.ced_htmlspecialchars($message).'</p></div>';
    echo '</body></html>';
    exit;
}

// Redirect
function redirect($destination_url, $message = '')
{
    global $ced_user, $ced_config;
    
    // Validate destination
    if (strpos($destination_url, 'http') !== 0)
        $destination_url = CED_BASE_URL.'/'.$destination_url;
    
    // If headers not sent, use header redirect
    if (!headers_sent())
    {
        header('Location: '.$destination_url);
        exit;
    }
    
    // Otherwise use meta refresh
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta http-equiv="refresh" content="1;url='.ced_htmlspecialchars($destination_url).'">';
    echo '<title>Redirecting...</title></head><body>';
    if ($message)
        echo '<p>'.ced_htmlspecialchars($message).'</p>';
    echo '<p>Redirecting... <a href="'.ced_htmlspecialchars($destination_url).'">Click here if not redirected</a></p>';
    echo '</body></html>';
    exit;
}

// Generate CSRF token
function generate_csrf_token()
{
    if (!isset($_SESSION))
        session_start();
    
    if (!isset($_SESSION['csrf_token']))
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token)
{
    if (!isset($_SESSION))
        session_start();
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Format time
function format_time($timestamp, $date_only = false)
{
    $format = $date_only ? 'Y-m-d' : 'Y-m-d H:i:s';
    return date($format, $timestamp);
}

// Get remote address
function get_remote_address()
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

