<?php

/**
 * Centralised database configuration.
 *
 * Reads connection parameters from environment variables so that no
 * credentials are ever hard-coded in source files.  A .env file in the
 * project root is loaded automatically during local development; in
 * production the variables should be injected by the hosting platform.
 */

declare(strict_types=1);

/**
 * Parse and load a .env file into $_ENV / getenv() if the variables are
 * not already set by the host environment.  Supports # comments and
 * values optionally wrapped in single or double quotes.
 */
function loadEnv(?string $path = null): void
{
    $path = $path ?? dirname(__DIR__, 2) . '/.env';

    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim(trim($value), "\"'");

        // Never override a value already set in the real environment.
        if (!array_key_exists($key, $_ENV) && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnv();

/**
 * Return a singleton PDO connection.
 *
 * PDO is configured with:
 *   - ERRMODE_EXCEPTION  – all errors throw PDOException
 *   - FETCH_ASSOC        – fetch rows as associative arrays by default
 *   - EMULATE_PREPARES=false – use native prepared statements
 *
 * Terminates with a generic 503 on failure so that internal connection
 * details are never exposed to the browser.
 */
function getDbConnection(): \PDO
{
    static $conn = null;

    if ($conn instanceof \PDO) {
        return $conn;
    }

    $host     = getenv('DB_HOST')     ?: 'localhost';
    $port     = (int)(getenv('DB_PORT') ?: 3306);
    $dbname   = getenv('DB_NAME')     ?: 'helping_paws2';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
        $conn = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (\PDOException $e) {
        error_log(sprintf(
            '[DB] Connection failed: %s (host=%s port=%d db=%s)',
            $e->getMessage(),
            $host,
            $port,
            $dbname
        ));
        http_response_code(503);
        die('Service temporarily unavailable. Please try again later.');
    }

    return $conn;
}
