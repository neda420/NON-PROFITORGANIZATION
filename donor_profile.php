<?php
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

// Fetch donor information
$donorId = $_GET['donorId'] ?? ''; // Using null coalescing operator to handle unset donorId
$sql = "SELECT * FROM donor_t WHERE donorId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Fetch donation statistics
$sql = "SELECT SUM(amount) AS total_amount, COUNT(*) AS total_donations FROM Donation_T WHERE donorId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$donation_stats = $result->fetch_assoc();

// Get the current directory path
$dir = dirname($_SERVER['PHP_SELF']);
$cssPath = $dir . '/css/donorprofile.css';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Profile</title>
    <link rel="stylesheet" href="/css/donorprofile.css">
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
                                <p><?php echo $row['fname'] . ' ' . $row['lname']; ?></p>
                            </div>
                            <div class="data">
                                <h4>Email</h4>
                                <p><?php echo $row['email']; ?></p>
                            </div>
                        </div>
                        <div class="info_data">
                            <div class="data">
                                <h4>Occupation</h4>
                                
                            </div>
                            
                        </div>
                        <div class="info_data">
                            <div class="data">
                                <h4>Contact Number</h4>
                                <p><?php echo $row['contactnumber']; ?></p>
                            </div>
                            <div class="data">
                                <h4>Address</h4>
                                <p><?php echo $row['house'] . ', ' . $row['street'] . ', ' . $row['thana'] . ', ' . $row['zip'] . ' ' . $row['city']; ?></p>
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
                                        <?php echo $donation_stats['total_amount']; ?>
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
                                        <?php echo $donation_stats['total_donations']; ?>
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
