<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/app.php';

use HelpingPaws\Models\AdminModel;

session_start();

// Already authenticated – redirect to panel.
if (!empty($_SESSION['admin_id'])) {
    header('Location: admin_panel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    enforceRateLimit('admin_login', 6, 300);

    $username = inputString($_POST['username'] ?? '', 50);
    $password = inputString($_POST['password'] ?? '', 255);

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $admin = (new AdminModel(getDbConnection()))->findByUsername($username);

        if ($admin !== null && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];

            logAudit('Admin logged in', [
                'admin_id' => $admin['id'],
                'ip'       => $_SERVER['REMOTE_ADDR'] ?? '-',
            ]);

            header('Location: admin_panel.php');
            exit;
        }

        logWarning('Admin login failed', [
            'username' => $username,
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? '-',
        ]);
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Helping Paws</title>
    <link rel="stylesheet" href="adminStyles.css">
</head>
<body>
<div class="login-container">
    <form class="login-form" action="admin_login.php" method="post" novalidate>
        <h2>Admin Login</h2>

        <?php if ($error !== '') : ?>
            <p class="error-message" role="alert"><?php echo e($error); ?></p>
        <?php endif; ?>

        <?php csrfTokenField(); ?>

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username"
                   required autocomplete="username" autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password"
                   required autocomplete="current-password">
        </div>

        <button type="submit">Login</button>
    </form>

    <a href="landingPage.html" class="home-button">HOME</a>
</div>
</body>
</html>
