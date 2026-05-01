<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/app.php';

use HelpingPaws\Models\DonationModel;

session_start();

// Require donor authentication.
require_once __DIR__ . '/src/Middleware/require_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Donation.html');
    exit;
}

verifyCsrfToken();
enforceRateLimit('donation_submit', 10, 300);

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

$ok = (new DonationModel(getDbConnection()))->create([
    'donor_id'         => $donorId,
    'donor_name'       => $donorName,
    'donation_purpose' => $purpose,
    'amount'           => $amount,
]);

if ($ok) {
    logInfo('Donation recorded', [
        'donor_id' => $donorId,
        'purpose'  => $purpose,
        'amount'   => $amount,
    ]);
    flashSet('success', 'Thank you! Your donation has been recorded.');
    header('Location: donor_profile.php');
} else {
    logError('Donation insert failed', ['donor_id' => $donorId]);
    flashSet('error', 'Could not record your donation. Please try again.');
    header('Location: Donation.html');
}

exit;
