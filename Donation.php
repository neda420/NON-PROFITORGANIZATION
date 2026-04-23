<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/flash.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

// Require donor authentication.
require_once __DIR__ . '/src/middleware/require_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Donation.html');
    exit;
}

verifyCsrfToken();

$donorId   = (string)$_SESSION['donorId'];
$donorName = inputString($_POST['donorName'] ?? '', 100);
$purpose   = inputString($_POST['purpose']   ?? '', 50);
$amount    = inputFloat($_POST['amount']     ?? '');

$allowedPurposes = ['Weekly', 'Monthly', 'Shelter', 'Medical'];
if ($donorName === '' || !in_array($purpose, $allowedPurposes, true) || $amount === null || $amount <= 0) {
    flashSet('error', 'Invalid donation data. Please try again.');
    header('Location: Donation.html');
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare(
    'INSERT INTO Donation_T (donor_id, donor_name, donation_purpose, amount)
     VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('sssd', $donorId, $donorName, $purpose, $amount);

if ($stmt->execute()) {
    logInfo('Donation recorded', [
        'donor_id' => $donorId,
        'purpose'  => $purpose,
        'amount'   => $amount,
    ]);
    flashSet('success', 'Thank you! Your donation has been recorded.');
    header('Location: donor_profile.php');
} else {
    logError('Donation insert failed', ['error' => $stmt->error]);
    flashSet('error', 'Could not record your donation. Please try again.');
    header('Location: Donation.html');
}

$stmt->close();
exit;
