<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "helping_paws2";
$port = "3307";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch donor ID from URL
$donorId = $_GET['donorId'] ?? ''; 

// Debug: Print donorId to check if it's passed correctly
echo "Donor ID: " . $donorId . "<br>";

// Fetch donor information
$sql = "SELECT donor_id, name, email, occupation, phone, address FROM donor_t WHERE donor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donorId);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
} else {
    echo "Error fetching donor information: " . $stmt->error . "<br>";
}

// Debug: Print row to check if data is fetched
echo "Fetched row: " . print_r($row, true) . "<br>";

// Fetch donation statistics
$sql = "SELECT SUM(amount) AS total_amount, COUNT(*) AS total_donations FROM Donation_T WHERE donor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donorId);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $donation_stats = $result->fetch_assoc();
} else {
    echo "Error fetching donation statistics: " . $stmt->error . "<br>";
}

// Debug: Print donation_stats to check if data is fetched
echo "Donation stats: " . print_r($donation_stats, true) . "<br>";

// Get the current host and path
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$cssPath = "css/donorprofile.css";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Profile</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>

<body>
    <div class="container">
        <div class="course-box">
            <div class="wrapper">
                <div class="right">
                    <div class="info">
                        <h3>Information</h3>
                        <div class="info_data">
                            <div class="data">
                                <h4>Full Name</h4>
                                <p><?php echo $row['name'] ?? 'N/A'; ?></p>
                            </div>
                            <div class="data">
                                <h4>Email</h4>
                                <p><?php echo $row['email'] ?? 'N/A'; ?></p>
                            </div>
                        </div>
                        <div class="info_data">
                            <div class="data">
                                <h4>Occupation</h4>
                                <p><?php echo $row['occupation'] ?? 'N/A'; ?></p>
                            </div>
                        </div>
                        <div class="info_data">
                            <div class="data">
                                <h4>Contact Number</h4>
                                <p><?php echo $row['phone'] ?? 'N/A'; ?></p>
                            </div>
                            <div class="data">
                                <h4>Address</h4>
                                <p><?php echo $row['address'] ?? 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ag-format-container">
                    <div class="ag-courses_box">
                        <div class="ag-courses_item">
                            <a href="#" class="ag-courses-item_link">
                                <div class="ag-courses-item_bg"></div>
                                <div class="ag-courses-item_title">
                                    Total Donation
                                </div>
                                <div class="ag-courses-item_date-box">
                                    BDT:
                                    <span class="ag-courses-item_date">
                                        <?php echo $donation_stats['total_amount'] ?? '0'; ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                        <div class="ag-courses_item">
                            <a href="#" class="ag-courses-item_link">
                                <div class="ag-courses-item_bg"></div>
                                <div class="ag-courses-item_title">
                                    Total Donation Made
                                </div>
                                <div class="ag-courses-item_date-box">
                                    Number of Donation:
                                    <span class="ag-courses-item_date">
                                        <?php echo $donation_stats['total_donations'] ?? '0'; ?>
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

<?php
// Close connection
$conn->close();
?>
