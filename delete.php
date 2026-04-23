<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/flash.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

// Admin only.
require_once __DIR__ . '/src/middleware/require_admin.php';

// CSRF token is passed as a query-string parameter for GET-based delete links.
$submittedToken = $_GET[csrfTokenName()] ?? '';
$expectedToken  = $_SESSION[csrfTokenName()] ?? '';

if (
    $submittedToken === ''
    || $expectedToken === ''
    || !hash_equals($expectedToken, $submittedToken)
) {
    http_response_code(403);
    die('Invalid security token.');
}

$id = inputPositiveInt($_GET['id'] ?? null);

if ($id === null) {
    flashSet('error', 'Invalid record ID.');
    header('Location: admin_panel.php');
    exit;
}

$conn = getDbConnection();

$stmt = $conn->prepare('DELETE FROM contact_messages WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    logAudit('Admin deleted contact message', [
        'admin_id'  => $_SESSION['admin_id'],
        'record_id' => $id,
    ]);
    flashSet('success', 'Record deleted successfully.');
} else {
    logError('Admin delete failed', ['error' => $stmt->error, 'record_id' => $id]);
    flashSet('error', 'Error deleting record.');
}

$stmt->close();
header('Location: admin_panel.php');
exit;
