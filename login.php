<?php
$servername = "localhost";
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "helping_paws2"; // Your database name
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["donorId"]) && isset($_POST["password"])) {
        $donor_id = $_POST["donorId"];
        $password = $_POST["password"];
        
        // To prevent SQL injection
        $donor_id = mysqli_real_escape_string($conn, $donor_id);
        $password = mysqli_real_escape_string($conn, $password);

        $sql = "SELECT * FROM donor_t WHERE donor_id = '$donor_id' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Login successful
            header("Location: Donor.html");
            exit();
        } else {
            // Login failed
            echo "Invalid donor ID or password!";
        }
    } else {
        echo "Donor ID or password not provided!";
    }
}

$conn->close();
?>
