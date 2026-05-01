<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/app.php';

use HelpingPaws\Models\DonorModel;

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

$model = new DonorModel(getDbConnection());

// Check donor ID uniqueness.
if ($model->existsById($donorId)) {
    flashSet('error', 'That donor ID is already taken. Please choose another.');
    header('Location: DonorCredentials.html');
    exit;
}

$ok = $model->create([
    'donor_id'             => $donorId,
    'name'                 => $name,
    'email'                => $email,
    'password'             => $passwordHash,
    'address'              => $address,
    'phone'                => $phone,
    'occupation'           => $occupation,
    'contact_method'       => $contactMethod,
    'interest_volunteering' => $interestVolunteering,
]);

if ($ok) {
    logInfo('Donor registered', ['donor_id' => $donorId]);
    flashSet('success', 'Account created successfully! Please log in.');
    header('Location: LoginDonor.html');
} else {
    logError('Donor registration failed', ['donor_id' => $donorId]);
    flashSet('error', 'Registration failed. Please try again.');
    header('Location: DonorCredentials.html');
}

exit;
