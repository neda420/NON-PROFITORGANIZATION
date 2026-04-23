<?php
/**
 * Application-wide configuration and bootstrapping.
 *
 * Include this file at the very top of every PHP entry-point BEFORE any
 * output is sent to the browser.  It configures PHP settings, starts the
 * session securely, and makes the database connection available.
 */

declare(strict_types=1);

// ── Error reporting ────────────────────────────────────────────────────────
// Show errors only in development; suppress in production.
$appEnv = getenv('APP_ENV') ?: 'production';
if ($appEnv === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}

// Write errors to a log file regardless of environment.
$logDir = dirname(__DIR__, 2) . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0750, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');

// ── Secure session configuration ───────────────────────────────────────────
$sessionName   = getenv('SESSION_NAME')     ?: 'HP_SESSION';
$sessionSecure = getenv('SESSION_SECURE') !== 'false'; // default true
$sessionSameSite = getenv('SESSION_SAMESITE') ?: 'Lax';

ini_set('session.name', $sessionName);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $sessionSecure ? '1' : '0');
ini_set('session.cookie_samesite', $sessionSameSite);
ini_set('session.gc_maxlifetime', (string)(getenv('SESSION_LIFETIME') ?: 3600));
ini_set('session.cookie_lifetime', '0');

// ── Load database config ───────────────────────────────────────────────────
require_once __DIR__ . '/database.php';
