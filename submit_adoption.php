<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "helping_paws2";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$MedicalRecord = $_POST['MedicalRecord'];
$AnimalType = $_POST['AnimalType'];
$AnimalGender = $_POST['AnimalGender']; // Ensure this matches the name attribute in the select tag
$VetBills = $_POST['VetBills'];

// Prepare SQL statement
$sql = "INSERT INTO RESCUED_ANIMALS (MedicalRecord, AnimalType, AnimalGender, VetBills) 
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// Check if statement preparation succeeded
if ($stmt) {
    $stmt->bind_param("sssd", $MedicalRecord, $AnimalType, $AnimalGender, $VetBills);
    
    // Execute SQL statement
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $stmt->error;
    }

    // Close statement
    $stmt->close();
} else {
    echo "Error preparing SQL statement: " . $conn->error;
}

// Close connection
$conn->close();
?>
