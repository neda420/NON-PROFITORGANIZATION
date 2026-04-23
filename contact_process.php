<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/flash.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: landingPage.html');
    exit;
}

verifyCsrfToken();

$name    = inputString($_POST['name']    ?? '', 100);
$email   = inputEmail($_POST['email']   ?? '');
$message = inputString($_POST['message'] ?? '', 2000);

if ($name === '' || $email === '' || $message === '') {
    flashSet('error', 'All fields are required.');
    header('Location: landingPage.html#contact-popup');
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare(
    'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
);
$stmt->bind_param('sss', $name, $email, $message);

if ($stmt->execute()) {
    logInfo('Contact message received', ['email' => $email]);
    flashSet('success', 'Your message has been sent. We will get back to you soon!');
} else {
    logError('Contact message insert failed', ['error' => $stmt->error]);
    flashSet('error', 'Could not send your message. Please try again.');
}

$stmt->close();
header('Location: landingPage.html');
exit;
