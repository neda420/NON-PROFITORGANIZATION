<?php

declare(strict_types=1);

/**
 * Simple per-session rate limiting.
 *
 * This protects sensitive handlers (logins/forms) from high-frequency abuse
 * without requiring Redis or database state.
 */
function enforceRateLimit(string $key, int $maxAttempts, int $windowSeconds): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $now = time();
    $_SESSION['_rate_limit'] ??= [];
    $_SESSION['_rate_limit'][$key] ??= [];

    $windowStart = $now - $windowSeconds;
    $attempts = array_values(array_filter(
        $_SESSION['_rate_limit'][$key],
        static fn (int $timestamp): bool => $timestamp >= $windowStart
    ));

    if (count($attempts) >= $maxAttempts) {
        http_response_code(429);
        header('Retry-After: ' . $windowSeconds);
        die('Too many requests. Please wait a moment and try again.');
    }

    $attempts[] = $now;
    $_SESSION['_rate_limit'][$key] = $attempts;
}
