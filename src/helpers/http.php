<?php
declare(strict_types=1);

/**
 * Shared HTTP response helpers for consistent handlers.
 */
function redirectTo(string $path, int $statusCode = 302): never
{
    header('Location: ' . $path, true, $statusCode);
    exit;
}

/**
 * Send JSON with explicit status and terminate.
 *
 * @param array<string, mixed> $payload
 */
function jsonResponse(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
