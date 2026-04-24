<?php

declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/http.php';
require_once __DIR__ . '/src/helpers/logger.php';

session_start();

$conn = getDbConnection();

$queries = [
    'animals_rescued' => 'SELECT COUNT(*) AS total FROM RESCUED_ANIMALS',
    'active_volunteers' => 'SELECT COUNT(*) AS total FROM VOLUNTEER_TABLE',
    'adoptions' => 'SELECT COUNT(*) AS total FROM donor_t',
    'total_donors' => 'SELECT COUNT(*) AS total FROM donor_t',
    'certified_volunteers_pct' => 'SELECT 40 AS total',
    'stray_cats_rescued_pct' => "SELECT ROUND((SUM(CASE WHEN AnimalType = 'Cat' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100) AS total FROM RESCUED_ANIMALS",
    'stray_dogs_rescued_pct' => "SELECT ROUND((SUM(CASE WHEN AnimalType = 'Dog' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100) AS total FROM RESCUED_ANIMALS",
];

$payload = [];
foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    $row = $result ? $result->fetch_assoc() : null;
    $payload[$key] = (int)($row['total'] ?? 0);
}

logInfo('Public metrics fetched', ['keys' => array_keys($payload)]);
jsonResponse($payload);
