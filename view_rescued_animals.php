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

// Fetch data from database
$sql = "SELECT MedicalRecord, AnimalType, AnimalGender, VetBills FROM RESCUED_ANIMALS";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rescued Animals</title>
    <link rel="stylesheet" href="animal_adoption.css">
</head>
<body>
    <div class="container">
        <h1>View Rescued Animals</h1>

        <table>
            <thead>
                <tr>
                    <th>Medical Record</th>
                    <th>Animal Type</th>
                    <th>Animal Gender</th>
                    <th>Vet Bills</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["MedicalRecord"] . "</td>";
                        echo "<td>" . $row["AnimalType"] . "</td>";
                        echo "<td>" . $row["AnimalGender"] . "</td>";
                        echo "<td>" . $row["VetBills"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No rescued animals found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <button onclick="window.location.href='animal_adoption.html'" class="btn">Make an entry for animals who need to be rescued</button>
    </div>
</body>
</html>


<?php
// Close connection
$conn->close();
?>
