<?php
/**
 * Simple PHP Git deploy script
 *
 * Automatically deploy the code using PHP and Git.
 *
 * @version 1.0.0
 * @link https://github.com/astappiev/simple-php-git-deploy/
 */

// =========================================[ Configuration start ]===========================================

/**
 * Protect the script from unauthorized access by using a secret access token.
 * If it's not present in the access URL as a GET variable named `sat`
 * e.g. deploy.php?sat=Bett...s the script is not going to deploy.
 *
 * @var string
 */
define('SECRET_ACCESS_TOKEN', 'YOUR_SECRET_HERE');

/**
 * The address of the remote Git repository that contains the code that's being
 * deployed.
 * If the repository is private, you'll need to use the SSH address.
 *
 * @var string
 */
define('REMOTE_REPOSITORY', 'git@github.com:astappiev/simple-php-git-deploy.git');

/**
 * The branch that's being deployed.
 * Must be present in the remote repository.
 *
 * @var string
 */
define('BRANCH', 'master');

/**
 * The location that the code is going to be deployed to.
 * Don't forget the trailing slash!
 *
 * @var string Full path including the trailing slash
 */
define('TARGET_DIR', '/home/astappiev/simple-php-git-deploy/');

/**
 * Time limit for each command.
 *
 * @var int Time in seconds
 */
define('TIME_LIMIT', 30);

/**
 * OPTIONAL
 * Email address to be notified on deployment failure.
 *
 * @var string A single email address, or comma separated list of email addresses
 *      e.g. 'someone@example.com' or 'someone@example.com, someone-else@example.com, ...'
 */
define('EMAIL_ON_ERROR', false);

// ===========================================[ Configuration end ]===========================================

$startTime = microtime(true);
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title>Simple PHP Git deploy script</title>
    <style>
        body { padding: 0 1em; background: #222; color: #fff; }
        h2, .error { color: #c33; }
        .prompt { color: #6be234; }
        .command { color: #729fcf; }
        .output { color: #999; }
    </style>
</head>
<body>
<?php
// retrieve the token
$token = null;
if (isset($_SERVER["HTTP_X_GITLAB_TOKEN"])) {
    $token = $_SERVER["HTTP_X_GITLAB_TOKEN"];
} elseif (isset($_GET["sat"])) {
    $token = $_GET["sat"];
}

if ($token !== SECRET_ACCESS_TOKEN) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
    die('<h2>ACCESS DENIED!</h2>');
}

?>
<pre>
Deploying <?php echo REMOTE_REPOSITORY; ?> <?php echo BRANCH."\n"; ?>
to        <?php echo TARGET_DIR; ?> ...

<?php
// The commands
$commands = [];

// ========================================[ Pre-Deployment steps ]========================================

$commands[] = 'git pull';
$commands[] = 'git show --summary';


// =======================================[ Run the command steps ]========================================
if (file_exists(TARGET_DIR) && is_dir(TARGET_DIR)) {
    chdir(TARGET_DIR); // Ensure that we're in the right directory
}


$output = '';
foreach ($commands as $command) {
    set_time_limit(TIME_LIMIT); // Reset the time limit for each command
    $tmp = [];
    exec($command.' 2>&1', $tmp, $return_code); // Execute the command
    // Output the result
    printf('
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
        , htmlentities(trim($command))
        , htmlentities(trim(implode("\n", $tmp)))
    );
    $output .= ob_get_contents();
    ob_flush(); // Try to output everything as it happens

    // Error handling and cleanup
    if ($return_code !== 0) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        printf('
<div class="error">
Error encountered!
Stopping the script to prevent possible data loss.
CHECK THE DATA IN YOUR TARGET DIR!
</div>
'
        );
        $error = sprintf(
            'Deployment error on %s using %s!'
            , $_SERVER['HTTP_HOST']
            , __FILE__
        );
        error_log($error);
        if (EMAIL_ON_ERROR) {
            $output .= ob_get_contents();
            $headers = [];
            $headers[] = sprintf('From: Simple PHP Git deploy script <simple-php-git-deploy@%s>', $_SERVER['HTTP_HOST']);
            $headers[] = sprintf('X-Mailer: PHP/%s', phpversion());
            mail(EMAIL_ON_ERROR, $error, strip_tags(trim($output)), implode("\r\n", $headers));
        }
        break;
    }
}
?>

Done in <?php echo (microtime(true) - $startTime) ?>.
</pre>
</body>
</html>
