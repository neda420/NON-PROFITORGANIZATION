<?php

declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/sanitize.php';
require_once __DIR__ . '/src/helpers/flash.php';

session_start();

// Require donor authentication.
require_once __DIR__ . '/src/middleware/require_auth.php';

$conn    = getDbConnection();
$donorId = (string)$_SESSION['donorId'];

// Only show donations for the logged-in donor.
$stmt = $conn->prepare(
    'SELECT donor_name, donation_purpose, amount, donation_date
       FROM Donation_T
      WHERE donor_id = ?
      ORDER BY donation_date DESC'
);
$stmt->bind_param('s', $donorId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation History | Helping Paws</title>
    <link rel="stylesheet" href="css/donor_history.css">
</head>
<body>
<div class="table-box">
    <?php flashRender(); ?>
    <div class="table-row table-head">
        <div class="table-cell first-cell"><p>Donor Name</p></div>
        <div class="table-cell"><p>Donation Purpose</p></div>
        <div class="table-cell"><p>Donation Amount (BDT)</p></div>
        <div class="table-cell last-cell"><p>Date</p></div>
    </div>

    <?php if ($result->num_rows > 0) : ?>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="table-row">
                <div class="table-cell first-cell"><p><?php echo e($row['donor_name']); ?></p></div>
                <div class="table-cell"><p><?php echo e($row['donation_purpose']); ?></p></div>
                <div class="table-cell"><p><?php echo e(number_format((float)$row['amount'], 2)); ?></p></div>
                <div class="table-cell last-cell"><p><?php echo e($row['donation_date'] ?? '—'); ?></p></div>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <p class="no-records">No donation records found.</p>
    <?php endif; ?>

    <?php $stmt->close(); ?>
</div>
</body>
</html>
