<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/logger.php';

session_start();

$adminId = $_SESSION['admin_id'] ?? null;

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

if ($adminId !== null) {
    logAudit('Admin logged out', ['admin_id' => $adminId]);
}

header('Location: admin_login.php');
exit;
