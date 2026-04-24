<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers/sanitize.php';
require_once __DIR__ . '/../src/helpers/csrf.php';
require_once __DIR__ . '/../src/helpers/rate_limit.php';

session_start();

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Assertion failed: {$message}\n");
        exit(1);
    }
}

$cleanEmail = inputEmail('user@example.com');
assertTrue($cleanEmail === 'user@example.com', 'Valid email should pass');
assertTrue(inputEmail('not-an-email') === '', 'Invalid email should be rejected');

$token1 = csrfToken();
$token2 = csrfToken();
assertTrue($token1 !== '', 'CSRF token should be generated');
assertTrue($token1 === $token2, 'CSRF token should remain stable per session');

enforceRateLimit('smoke_rate_limit', 3, 30);
enforceRateLimit('smoke_rate_limit', 3, 30);
enforceRateLimit('smoke_rate_limit', 3, 30);

echo "Smoke tests passed.\n";
