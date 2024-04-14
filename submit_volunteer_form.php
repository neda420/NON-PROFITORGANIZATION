<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "helping_paws2";
$port = "3307";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

// Prepare SQL statement
$sql = "INSERT INTO VOLUNTEER_TABLE (name, email, phone, message) VALUES (?, ?, ?, ?)";

// Prepare and bind parameters
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $phone, $message);

// Execute the statement
$insertResult = $stmt->execute();

// Check if the record was inserted successfully
if ($insertResult) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Fetch total number of volunteers
$sql_count = "SELECT COUNT(*) as total FROM VOLUNTEER_TABLE";
$result = $conn->query($sql_count);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalVolunteers = $row['total'];
} else {
    $totalVolunteers = 0; // Default value if no volunteers
}

// Close the statement
$stmt->close();

// Close the connection
$conn->close();

// Return total number of volunteers as JSON
echo json_encode(array('totalVolunteers' => $totalVolunteers));
?>
