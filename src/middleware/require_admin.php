<?php
/**
 * Admin authentication middleware.
 *
 * Include this file at the top of any admin page.  It will redirect
 * unauthenticated visitors to the admin login page.
 *
 * Usage:
 *   require_once __DIR__ . '/../src/middleware/require_admin.php';
 */

declare(strict_types=1);

require_once __DIR__ . '/../helpers/http.php';

// session_start() must have already been called (done in app.php).
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    redirectTo('admin_login.php');
}
