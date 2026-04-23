<?php
/**
 * Donor authentication middleware.
 *
 * Include this file at the top of any page that requires the donor to be
 * logged in.  It will redirect unauthenticated visitors to the login page.
 *
 * Usage:
 *   require_once __DIR__ . '/src/middleware/require_auth.php';
 */

declare(strict_types=1);

// session_start() must have already been called (done in app.php).
if (!isset($_SESSION['donorId']) || empty($_SESSION['donorId'])) {
    $loginUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . '/LoginDonor.html';

    header('Location: ' . $loginUrl);
    exit;
}
