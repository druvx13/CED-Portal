<?php

/**
 * CED Portal - Register Page
 * Adapted from FluxBB architecture
 */

define('CED_ROOT', './');
require CED_ROOT.'include/common.php';

// If already logged in, redirect
if (!$ced_user['is_guest'])
    redirect('index.php');

$errors = array();

// Handle registration form submission
if (isset($_POST['form_sent']))
{
    $username = isset($_POST['req_username']) ? trim($_POST['req_username']) : '';
    $password = isset($_POST['req_password']) ? $_POST['req_password'] : '';
    $password2 = isset($_POST['req_password2']) ? $_POST['req_password2'] : '';
    $email = isset($_POST['req_email']) ? strtolower(trim($_POST['req_email'])) : '';
    
    // Validate input
    if (empty($username))
        $errors[] = 'You must enter a username.';
    elseif (strlen($username) < 3 || strlen($username) > 25)
        $errors[] = 'Username must be between 3 and 25 characters.';
    
    if (empty($password))
        $errors[] = 'You must enter a password.';
    elseif (strlen($password) < 6)
        $errors[] = 'Password must be at least 6 characters long.';
    
    if ($password != $password2)
        $errors[] = 'Passwords do not match.';
    
    if (empty($email))
        $errors[] = 'You must enter an email address.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email address.';
    
    // Check if username already exists
    if (empty($errors))
    {
        $result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE username=\''.$db->escape($username).'\'');
        if ($db->num_rows($result) > 0)
            $errors[] = 'Username already exists. Please choose another.';
    }
    
    // Check if email already exists
    if (empty($errors))
    {
        $result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE email=\''.$db->escape($email).'\'');
        if ($db->num_rows($result) > 0)
            $errors[] = 'Email already registered. Please use another email address.';
    }
    
    // Register if no errors
    if (empty($errors))
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $now = time();
        
        $db->query('INSERT INTO '.$db->prefix.'users (username, password, email, group_id, registered, registration_ip, last_visit) VALUES (\''.$db->escape($username).'\', \''.$db->escape($password_hash).'\', \''.$db->escape($email).'\', '.CED_STUDENT.', '.$now.', \''.$db->escape(get_remote_address()).'\', '.$now.')');
        
        $user_id = $db->insert_id();
        
        if ($user_id)
        {
            // Set cookie
            $expire = time() + 31536000; // 1 year
            ced_setcookie($user_id, $password_hash, $expire);
            
            // Redirect to index
            redirect('index.php', 'Registration successful! Welcome to CED Portal.');
        }
        else
        {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$page_title = 'Register';
$page = 'register';

require CED_ROOT.'header.php';

?>

<div class="blockform">
    <h2><span>Register</span></h2>
    <div class="box">
        <form method="post" action="register.php">
            <div class="inform">
                <?php if (!empty($errors)): ?>
                <div class="forminfo error-info">
                    <h3>Registration failed</h3>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo ced_htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <fieldset>
                    <legend>Enter your details</legend>
                    <div class="infldset">
                        <input type="hidden" name="form_sent" value="1" />
                        
                        <label class="required"><strong>Username <span>(Required)</span></strong><br />
                        <input type="text" name="req_username" size="25" maxlength="25" value="<?php echo isset($_POST['req_username']) ? ced_htmlspecialchars($_POST['req_username']) : '' ?>" /><br /></label>
                        
                        <label class="required"><strong>Email <span>(Required)</span></strong><br />
                        <input type="email" name="req_email" size="50" maxlength="80" value="<?php echo isset($_POST['req_email']) ? ced_htmlspecialchars($_POST['req_email']) : '' ?>" /><br /></label>
                        
                        <label class="required"><strong>Password <span>(Required)</span></strong><br />
                        <input type="password" name="req_password" size="25" /><br /></label>
                        
                        <label class="required"><strong>Confirm Password <span>(Required)</span></strong><br />
                        <input type="password" name="req_password2" size="25" /><br /></label>
                        
                        <div class="clearer"></div>
                    </div>
                </fieldset>
            </div>
            <p class="buttons"><input type="submit" name="register" value="Register" /></p>
        </form>
    </div>
</div>

<div class="block">
    <div class="box">
        <div class="inbox">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php

require CED_ROOT.'footer.php';

