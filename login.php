<?php

declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/flash.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/helpers/rate_limit.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

// Already logged in – redirect to dashboard.
if (!empty($_SESSION['donorId'])) {
    header('Location: DonorLanding.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: LoginDonor.html');
    exit;
}

verifyCsrfToken();
enforceRateLimit('donor_login', 8, 300);

$donorId  = inputNumericString($_POST['donorId'] ?? '');
$password = inputString($_POST['password'] ?? '', 255);

if ($donorId === '' || $password === '') {
    flashSet('error', 'Donor ID and password are required.');
    header('Location: LoginDonor.html');
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare(
    'SELECT donor_id, password FROM donor_t WHERE donor_id = ? LIMIT 1'
);
$stmt->bind_param('s', $donorId);
$stmt->execute();
$result = $stmt->get_result();
$donor  = $result->fetch_assoc();
$stmt->close();

if ($donor === null || !password_verify($password, $donor['password'])) {
    logWarning('Donor login failed', [
        'donor_id' => $donorId,
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? '-',
    ]);
    flashSet('error', 'Invalid donor ID or password.');
    header('Location: LoginDonor.html');
    exit;
}

// Regenerate session ID on privilege change to prevent session fixation.
session_regenerate_id(true);

$_SESSION['donorId'] = $donor['donor_id'];

logInfo('Donor logged in', ['donor_id' => $donor['donor_id']]);

header('Location: DonorLanding.php');
exit;
