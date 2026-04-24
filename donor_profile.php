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

$stmt = $conn->prepare(
    'SELECT donor_id, name, email, occupation, phone, address
       FROM donor_t
      WHERE donor_id = ?
      LIMIT 1'
);
$stmt->bind_param('s', $donorId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare(
    'SELECT COALESCE(SUM(amount), 0) AS total_amount,
            COUNT(*)                 AS total_donations
       FROM Donation_T
      WHERE donor_id = ?'
);
$stmt->bind_param('s', $donorId);
$stmt->execute();
$donationStats = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Profile | Helping Paws</title>
    <link rel="stylesheet" href="css/donorprofile.css">
</head>
<body>
<div class="container">
    <?php flashRender(); ?>
    <div class="course-box">
        <div class="wrapper">
            <div class="right">
                <div class="info">
                    <h2>Profile Information</h2>
                    <div class="info_data">
                        <div class="data">
                            <h4>Full Name</h4>
                            <p><?php echo e($row['name'] ?? ''); ?></p>
                        </div>
                        <div class="data">
                            <h4>Email</h4>
                            <p><?php echo e($row['email'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="info_data">
                        <div class="data">
                            <h4>Occupation</h4>
                            <p><?php echo e($row['occupation'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="info_data">
                        <div class="data">
                            <h4>Contact Number</h4>
                            <p><?php echo e($row['phone'] ?? ''); ?></p>
                        </div>
                        <div class="data">
                            <h4>Address</h4>
                            <p><?php echo e($row['address'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ag-format-container">
                <div class="ag-courses_box">
                    <div class="ag-courses_item">
                        <a href="#" class="ag-courses-item_link">
                            <div class="ag-courses-item_bg"></div>
                            <div class="ag-courses-item_title">Total Donated</div>
                            <div class="ag-courses-item_date-box">
                                BDT: <span class="ag-courses-item_date">
                                    <?php echo e(number_format((float)($donationStats['total_amount'] ?? 0), 2)); ?>
                                </span>
                            </div>
                        </a>
                    </div>
                    <div class="ag-courses_item">
                        <a href="#" class="ag-courses-item_link">
                            <div class="ag-courses-item_bg"></div>
                            <div class="ag-courses-item_title">Donations Made</div>
                            <div class="ag-courses-item_date-box">
                                Count: <span class="ag-courses-item_date">
                                    <?php echo e($donationStats['total_donations'] ?? 0); ?>
                                </span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
