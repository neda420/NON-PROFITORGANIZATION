<?php
// Database connection
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";
$port = "3307";
$conn = new mysqli($servername, $username, $password, $dbname,$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch donor information
$donorId = $_GET['donorId']; // Assuming donorId is obtained from URL
$sql = "SELECT * FROM donor_t WHERE donorId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Close statement
$stmt->close();

// Fetch donation statistics
$sql = "SELECT SUM(amount) AS total_amount, COUNT(*) AS total_donations FROM Donation_T WHERE donorId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();
$donation_stats = $result->fetch_assoc();

// Close statement
$stmt->close();

// Close connection
$conn->close();
?>
