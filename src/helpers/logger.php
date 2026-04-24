<?php

/**
 * Structured application logger.
 *
 * Writes newline-delimited JSON records to logs/app.log so that log
 * aggregation tools (Loki, CloudWatch, etc.) can ingest them without
 * additional parsing.
 *
 * Usage:
 *   appLog('info',  'Donor registered', ['donor_id' => $id]);
 *   appLog('error', 'Login failed',     ['donor_id' => $id, 'ip' => $_SERVER['REMOTE_ADDR']]);
 */

declare(strict_types=1);

/**
 * Write a structured log record.
 *
 * @param string               $level   One of: debug, info, warning, error, critical.
 * @param string               $message Human-readable description of the event.
 * @param array<string, mixed> $context Additional key/value pairs to include.
 */
function appLog(string $level, string $message, array $context = []): void
{
    static $logFile = null;

    if ($logFile === null) {
        $logDir  = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }
        $logFile = $logDir . '/app.log';
    }

    $record = array_merge(
        [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'level'     => strtoupper($level),
            'message'   => $message,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? session_id() ?: '-',
            'ip'         => $_SERVER['REMOTE_ADDR']        ?? '-',
            'method'     => $_SERVER['REQUEST_METHOD']     ?? '-',
            'uri'        => $_SERVER['REQUEST_URI']        ?? '-',
        ],
        $context
    );

    $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

    // Append; ignore failures silently to avoid disrupting the request.
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Convenience wrappers.
 */
function logInfo(string $message, array $context = []): void
{
    appLog('info', $message, $context);
}

function logWarning(string $message, array $context = []): void
{
    appLog('warning', $message, $context);
}

function logError(string $message, array $context = []): void
{
    appLog('error', $message, $context);
}

function logAudit(string $message, array $context = []): void
{
    appLog('audit', $message, $context);
}
