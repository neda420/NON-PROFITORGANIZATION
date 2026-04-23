<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/logger.php';

session_start();

$donorId = $_SESSION['donorId'] ?? null;

// Wipe all session data.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

if ($donorId !== null) {
    logInfo('Donor logged out', ['donor_id' => $donorId]);
}

header('Location: LoginDonor.html');
exit;
