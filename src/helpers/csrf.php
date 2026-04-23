<?php
/**
 * CSRF protection helpers.
 *
 * Usage:
 *   // Generate and embed token in a form:
 *   csrfTokenField();
 *
 *   // Validate on the receiving endpoint (dies on failure):
 *   verifyCsrfToken();
 */

declare(strict_types=1);

/**
 * Return the name of the CSRF token field / session key.
 */
function csrfTokenName(): string
{
    return '_csrf_token';
}

/**
 * Generate (or retrieve) the per-session CSRF token.
 *
 * The session must already be started before calling this function.
 */
function csrfToken(): string
{
    $key = csrfTokenName();

    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }

    return $_SESSION[$key];
}

/**
 * Render a hidden `<input>` element carrying the CSRF token.
 * Safe to call inside any HTML form.
 */
function csrfTokenField(): void
{
    $token = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');
    $name  = htmlspecialchars(csrfTokenName(), ENT_QUOTES, 'UTF-8');
    echo "<input type=\"hidden\" name=\"{$name}\" value=\"{$token}\">\n";
}

/**
 * Verify the CSRF token submitted with a POST request.
 *
 * Sends a 403 and terminates execution if the token is missing or does
 * not match.  Call this at the very beginning of every state-changing
 * (POST/PUT/DELETE) handler.
 */
function verifyCsrfToken(): void
{
    $submitted = $_POST[csrfTokenName()] ?? '';
    $expected  = $_SESSION[csrfTokenName()] ?? '';

    if (
        $submitted === ''
        || $expected === ''
        || !hash_equals($expected, $submitted)
    ) {
        http_response_code(403);
        die('Invalid or missing security token. Please go back and try again.');
    }
}
