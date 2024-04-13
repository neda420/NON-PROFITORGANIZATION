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

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $donorid = $_POST["donorid"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $occupation = $_POST["occupation"];
    $contactMethod = $_POST["contactMethod"];
    $interestVolunteering = $_POST["interestVolunteering"];

    // SQL query to insert data into DONOR_T table
    $sql = "INSERT INTO DONOR_T (donor_id, name, email, password, address, phone, occupation, contact_method, interest_volunteering) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $donorid, $name, $email, $password, $address, $phone, $occupation, $contactMethod, $interestVolunteering);

    // Execute the query
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
