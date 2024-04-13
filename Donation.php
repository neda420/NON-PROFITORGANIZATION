<?php
// Database connection
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";
$port=3307
$conn = new mysqli($servername, $username, $password, $dbname,$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donorId = $_POST["donorId"];
    $donorName = $_POST["donorName"];
    $purpose = $_POST["purpose"];
    $amount = $_POST["amount"];
    $date = $_POST["date"];

    // SQL query to insert data into Donation_T table
    $sql = "INSERT INTO Donation_T (donor_id, donor_name, donation_purpose, amount, donation_date) 
            VALUES (?, ?, ?, ?, ?)";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $donorId, $donorName, $purpose, $amount, $date);

    // Execute the query
    if ($stmt->execute()) {
        echo "Donation recorded successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
