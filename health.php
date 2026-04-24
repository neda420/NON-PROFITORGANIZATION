<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/http.php';

session_start();

try {
    $conn = getDbConnection();
    $result = $conn->query('SELECT 1 AS ok');
    $row = $result ? $result->fetch_assoc() : null;
    $dbOk = (int)($row['ok'] ?? 0) === 1;

    if (!$dbOk) {
        jsonResponse(['status' => 'degraded', 'database' => 'unhealthy'], 503);
    }

    jsonResponse(['status' => 'ok', 'database' => 'healthy'], 200);
} catch (Throwable) {
    jsonResponse(['status' => 'degraded', 'database' => 'unhealthy'], 503);
}
