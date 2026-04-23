<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/flash.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

// Require admin authentication.
require_once __DIR__ . '/src/middleware/require_admin.php';

$conn = getDbConnection();
$updateMessage = '';

// Handle record update.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    verifyCsrfToken();

    $updateId   = inputPositiveInt($_POST['update_id'] ?? '');
    $newName    = inputString($_POST['new_name']    ?? '', 100);
    $newEmail   = inputEmail($_POST['new_email']    ?? '');
    $newMessage = inputString($_POST['new_message'] ?? '', 1000);
    $adminId    = filter_var($_SESSION['admin_id'] ?? '', FILTER_VALIDATE_INT);

    if ($updateId === null || $newName === '' || $newEmail === '' || $adminId === false || $adminId < 1) {
        $updateMessage = 'Invalid input supplied.';
    } else {
        $stmt = $conn->prepare(
            'UPDATE contact_messages SET name=?, email=?, message=?, admin_id=? WHERE id=?'
        );
        $stmt->bind_param('sssii', $newName, $newEmail, $newMessage, $adminId, $updateId);

        if ($stmt->execute()) {
            $updateMessage = 'Record updated successfully.';
            logAudit('Admin updated contact message', [
                'admin_id'  => $adminId,
                'record_id' => $updateId,
            ]);
        } else {
            $updateMessage = 'Error updating record.';
            logError('Admin update failed', ['error' => $stmt->error]);
        }
        $stmt->close();
    }
}

// Fetch all contact messages.
$result = $conn->query(
    'SELECT id, name, email, message, created_at, admin_id FROM contact_messages ORDER BY created_at DESC'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel – Contact Messages | Helping Paws</title>
    <link rel="stylesheet" href="adminStyles.css">
</head>
<body>
<div class="admin-container">
    <h1>Contact Messages</h1>

    <a href="landingPage.html" class="home-button">HOME</a>
    <a href="admin_logout.php" class="logout-button">Logout</a>

    <?php if ($updateMessage !== ''): ?>
        <p class="update-message"><?php echo e($updateMessage); ?></p>
    <?php endif; ?>

    <?php flashRender(); ?>

    <table>
        <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Message</th>
            <th scope="col">Created At</th>
            <th scope="col">Edited By</th>
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo e($row['id']); ?></td>
                <td><?php echo e($row['name']); ?></td>
                <td><?php echo e($row['email']); ?></td>
                <td><?php echo e($row['message']); ?></td>
                <td><?php echo e($row['created_at']); ?></td>
                <td><?php echo $row['admin_id'] ? e($row['admin_id']) : 'None'; ?></td>
                <td>
                    <button type="button"
                        onclick="editRow(
                            <?php echo (int)$row['id']; ?>,
                            <?php echo json_encode($row['name'],    JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                            <?php echo json_encode($row['email'],   JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                            <?php echo json_encode($row['message'], JSON_HEX_APOS | JSON_HEX_QUOT); ?>
                        )">Edit</button>
                    |
                    <a href="delete.php?id=<?php echo (int)$row['id']; ?>&<?php echo e(csrfTokenName()); ?>=<?php echo e(csrfToken()); ?>"
                       onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Edit Form -->
    <form id="editForm" action="admin_panel.php" method="post" style="display:none;">
        <?php csrfTokenField(); ?>
        <input type="hidden" id="update_id" name="update_id">
        <label for="new_name">Name</label>
        <input type="text"  id="new_name"    name="new_name"    required maxlength="100">
        <label for="new_email">Email</label>
        <input type="email" id="new_email"   name="new_email"   required maxlength="100">
        <label for="new_message">Message</label>
        <textarea           id="new_message" name="new_message" required maxlength="1000"></textarea>
        <button type="submit">Update</button>
        <button type="button" onclick="document.getElementById('editForm').style.display='none'">Cancel</button>
    </form>
</div>

<script>
    function editRow(id, name, email, message) {
        document.getElementById('update_id').value   = id;
        document.getElementById('new_name').value    = name;
        document.getElementById('new_email').value   = email;
        document.getElementById('new_message').value = message;
        document.getElementById('editForm').style.display = 'block';
    }
</script>
</body>
</html>
