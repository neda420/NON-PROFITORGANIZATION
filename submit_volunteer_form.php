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
    header('Location: index.html');
    exit;
}

verifyCsrfToken();
enforceRateLimit('volunteer_form', 5, 300);

$name    = inputString($_POST['name']    ?? '', 100);
$email   = inputEmail($_POST['email']   ?? '');
$phone   = inputNumericString($_POST['phone']   ?? '', 20);
$message = inputString($_POST['message'] ?? '', 1000);

if ($name === '' || $email === '') {
    flashSet('error', 'Name and email are required.');
    header('Location: index.html');
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare(
    'INSERT INTO VOLUNTEER_TABLE (name, email, phone, message) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $name, $email, $phone, $message);

if ($stmt->execute()) {
    logInfo('Volunteer registered', ['email' => $email]);
    flashSet('success', 'Thank you for signing up as a volunteer! We will be in touch.');
} else {
    logError('Volunteer insert failed', ['error' => $stmt->error]);
    flashSet('error', 'Registration failed. Please try again.');
}

$stmt->close();
header('Location: index.html');
exit;
