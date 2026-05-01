<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/app.php';

use HelpingPaws\Models\DonorModel;

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

$donor = (new DonorModel(getDbConnection()))->findForAuth($donorId);

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
