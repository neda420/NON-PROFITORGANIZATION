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

// Fetch donor history from database
$sql = "SELECT donor_name, donation_purpose, amount FROM Donation_T";
$result = $conn->query($sql);

// Get the current directory path
$dir = dirname($_SERVER['PHP_SELF']);
$cssPath = $dir . '/css/donor_history.css';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor History</title>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>
<body>
    <div class="table-box">
        <div class="table-row table-head">
            <div class="table-cell first-cell">
                <p>Donor Name</p>
            </div>
            <div class="table-cell">
                <p>Donation Purpose</p>
            </div>
            <div class="table-cell">
                <p>Donation Amount</p>
            </div>
            <div class="table-cell last-cell">
                <p>Date</p>
            </div>
        </div>

        <?php
        // Display donor history dynamically
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="table-row">';
                echo '<div class="table-cell first-cell"><p>' . $row["donor_name"] . '</p></div>';
                echo '<div class="table-cell"><p>' . $row["donation_purpose"] . '</p></div>';
                echo '<div class="table-cell"><p>' . $row["amount"] . '</p></div>';
                echo '<div class="table-cell last-cell"><a> Update </a> <a> Delete </a></div>';
                echo '</div>';
            }
        } else {
            echo '<p>No records found.</p>';
        }
        ?>

    </div>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
