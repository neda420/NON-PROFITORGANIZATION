<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/flash.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/helpers/rate_limit.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: DonorCredentials.html');
    exit;
}

verifyCsrfToken();
enforceRateLimit('donor_registration', 5, 600);

// Collect and validate inputs.
$name                 = inputString($_POST['name']                 ?? '', 100);
$email                = inputEmail($_POST['email']                 ?? '');
$rawPassword          = inputString($_POST['password']             ?? '', 255);
$donorId              = inputNumericString($_POST['donorid']        ?? '', 10);
$address              = inputString($_POST['address']              ?? '', 200);
$phone                = inputNumericString($_POST['phone']          ?? '', 20);
$occupation           = inputString($_POST['occupation']           ?? '', 50);
$contactMethod        = inputString($_POST['contactMethod']        ?? '', 20);
$interestVolunteering = inputString($_POST['interestVolunteering'] ?? '', 10);

// Required field validation.
if ($name === '' || $email === '' || $rawPassword === '' || $donorId === '') {
    flashSet('error', 'Name, email, password and donor ID are required.');
    header('Location: DonorCredentials.html');
    exit;
}

if (strlen($rawPassword) < 8) {
    flashSet('error', 'Password must be at least 8 characters.');
    header('Location: DonorCredentials.html');
    exit;
}

$passwordHash = password_hash($rawPassword, PASSWORD_BCRYPT);

$conn = getDbConnection();

// Check donor ID uniqueness.
$check = $conn->prepare('SELECT donor_id FROM donor_t WHERE donor_id = ? LIMIT 1');
$check->bind_param('s', $donorId);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    flashSet('error', 'That donor ID is already taken. Please choose another.');
    header('Location: DonorCredentials.html');
    exit;
}
$check->close();

$stmt = $conn->prepare(
    'INSERT INTO donor_t
        (donor_id, name, email, password, address, phone, occupation, contact_method, interest_volunteering)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'sssssssss',
    $donorId, $name, $email, $passwordHash,
    $address, $phone, $occupation, $contactMethod, $interestVolunteering
);

if ($stmt->execute()) {
    logInfo('Donor registered', ['donor_id' => $donorId]);
    flashSet('success', 'Account created successfully! Please log in.');
    header('Location: LoginDonor.html');
} else {
    logError('Donor registration failed', ['error' => $stmt->error]);
    flashSet('error', 'Registration failed. Please try again.');
    header('Location: DonorCredentials.html');
}

$stmt->close();
exit;
