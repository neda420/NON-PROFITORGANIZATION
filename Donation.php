<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "helping_paws2";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

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

    // SQL query to insert data into Donation_T table
    $sql = "INSERT INTO Donation_T (donor_id, donor_name, donation_purpose, amount) 
            VALUES (?, ?, ?, ?)";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }

    $bindResult = $stmt->bind_param("sssd", $donorId, $donorName, $purpose, $amount);
    
    if ($bindResult === false) {
        echo "Error binding parameters: " . $stmt->error;
        exit;
    }

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
