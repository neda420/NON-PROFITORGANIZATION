<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/http.php';

session_start();

$conn   = getDbConnection();
$result = $conn->query('SELECT COUNT(*) AS total FROM VOLUNTEER_TABLE');
$row    = $result ? $result->fetch_assoc() : null;

jsonResponse(['totalVolunteers' => (int)($row['total'] ?? 0)]);
