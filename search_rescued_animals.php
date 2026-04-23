<?php
declare(strict_types=1);

require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/helpers/sanitize.php';

session_start();

$conn    = getDbConnection();
$rows    = [];
$search  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search = inputString($_POST['search'] ?? '', 100);

    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $conn->prepare(
            'SELECT MedicalRecord, AnimalType, AnimalGender, VetBills
               FROM RESCUED_ANIMALS
              WHERE AnimalType LIKE ? OR AnimalGender LIKE ?'
        );
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rescued Animals | Helping Paws</title>
    <link rel="stylesheet" href="animal_adoption.css">
</head>
<body>
<div class="container">
    <h1>Search Results</h1>

    <?php if ($search !== ''): ?>
        <p>Results for: <strong><?php echo e($search); ?></strong></p>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th scope="col">Medical Record</th>
            <th scope="col">Animal Type</th>
            <th scope="col">Animal Gender</th>
            <th scope="col">Vet Bills</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows !== []): ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo e($row['MedicalRecord']); ?></td>
                    <td><?php echo e($row['AnimalType']); ?></td>
                    <td><?php echo e($row['AnimalGender']); ?></td>
                    <td><?php echo e($row['VetBills']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No results found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="landingPage.html" class="btn">Back to Home</a>
</div>
</body>
</html>
