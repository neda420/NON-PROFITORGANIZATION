<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';

session_start();

$conn   = getDbConnection();
$result = $conn->query('SELECT COUNT(*) AS total FROM VOLUNTEER_TABLE');
$row    = $result ? $result->fetch_assoc() : null;

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['totalVolunteers' => (int)($row['total'] ?? 0)]);
exit;
