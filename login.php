<?php

/**
 * CED Portal - Login Page
 * Adapted from FluxBB architecture
 */

define('CED_ROOT', './');
require CED_ROOT.'include/common.php';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'out')
{
    // Remove cookie
    ced_setcookie(1, '', time() - 31536000);
    
    // Redirect to index
    redirect('index.php', 'You have been logged out.');
}

// If already logged in, redirect
if (!$ced_user['is_guest'])
    redirect('index.php');

$errors = array();

// Handle login form submission
if (isset($_POST['form_sent']))
{
    $username = isset($_POST['req_username']) ? trim($_POST['req_username']) : '';
    $password = isset($_POST['req_password']) ? $_POST['req_password'] : '';
    
    // Validate input
    if (empty($username))
        $errors[] = 'You must enter a username.';
    
    if (empty($password))
        $errors[] = 'You must enter a password.';
    
    // Authenticate if no errors
    if (empty($errors))
    {
        if (authenticate_user($username, $password))
        {
            // Set cookie
            $expire = time() + 31536000; // 1 year
            ced_setcookie($ced_user['id'], $ced_user['password'], $expire);
            
            // Redirect to index
            redirect('index.php', 'Login successful!');
        }
        else
        {
            $errors[] = 'Invalid username or password.';
        }
    }
}

$page_title = 'Login';
$page = 'login';

require CED_ROOT.'header.php';

?>

<div class="blockform">
    <h2><span>Login</span></h2>
    <div class="box">
        <form method="post" action="login.php">
            <div class="inform">
                <?php if (!empty($errors)): ?>
                <div class="forminfo error-info">
                    <h3>Login failed</h3>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo ced_htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <fieldset>
                    <legend>Enter your username and password</legend>
                    <div class="infldset">
                        <input type="hidden" name="form_sent" value="1" />
                        
                        <label class="conl required"><strong>Username <span>(Required)</span></strong><br />
                        <input type="text" name="req_username" size="25" maxlength="25" value="<?php echo isset($_POST['req_username']) ? ced_htmlspecialchars($_POST['req_username']) : '' ?>" /><br /></label>
                        
                        <label class="conl required"><strong>Password <span>(Required)</span></strong><br />
                        <input type="password" name="req_password" size="25" /><br /></label>
                        
                        <div class="clearer"></div>
                    </div>
                </fieldset>
            </div>
            <p class="buttons"><input type="submit" name="login" value="Login" /></p>
        </form>
    </div>
</div>

<div class="block">
    <div class="box">
        <div class="inbox">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php

require CED_ROOT.'footer.php';

