<?php
// Database connection details
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "helping_paws2";
$port = "3307";

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete record from database
    $sql = "DELETE FROM contact_messages WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Record deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting record: " . $conn->error . "');</script>";
    }
}

// Close the database connection
$conn->close();

// Redirect back to admin_panel.php after deletion
header("Location: admin_panel.php");
exit;
?>
