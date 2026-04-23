<?php
/**
 * Flash message helpers.
 *
 * Flash messages are stored in the session and consumed (removed) on the
 * next page load, making them ideal for showing success/error feedback
 * after a redirect.
 *
 * Usage:
 *   flashSet('success', 'Your donation was recorded.');
 *   header('Location: donor_profile.php');
 *   exit;
 *
 *   // On the target page:
 *   flashRender();
 */

declare(strict_types=1);

/**
 * Store a flash message in the session.
 *
 * @param string $type    One of 'success', 'error', 'warning', 'info'.
 * @param string $message Human-readable text (will be HTML-escaped on render).
 */
function flashSet(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Render and clear all pending flash messages as styled HTML.
 * Safe to call even when there are no messages.
 */
function flashRender(): void
{
    if (empty($_SESSION['_flash'])) {
        return;
    }

    $messages = $_SESSION['_flash'];
    unset($_SESSION['_flash']);

    $typeMap = [
        'success' => 'flash-success',
        'error'   => 'flash-error',
        'warning' => 'flash-warning',
        'info'    => 'flash-info',
    ];

    echo '<div class="flash-messages" role="alert">' . "\n";
    foreach ($messages as $flash) {
        $cssClass = $typeMap[$flash['type']] ?? 'flash-info';
        $msg      = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        echo "  <div class=\"flash-message {$cssClass}\">{$msg}</div>\n";
    }
    echo '</div>' . "\n";
}
