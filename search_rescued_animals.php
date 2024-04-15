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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search = $_POST["search"];
    
    // Fetch data from database based on search input
    $sql = "SELECT MedicalRecord, AnimalType, AnimalGender, VetBills FROM RESCUED_ANIMALS WHERE AnimalType LIKE '%$search%' OR AnimalGender LIKE '%$search%'";
    $result = $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Rescued Animals</title>
    <link rel="stylesheet" href="animal_adoption.css">
</head>
<body>
    <div class="container">
        <h1>Search Results</h1>

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
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["MedicalRecord"] . "</td>";
                        echo "<td>" . $row["AnimalType"] . "</td>";
                        echo "<td>" . $row["AnimalGender"] . "</td>";
                        echo "<td>" . $row["VetBills"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No results found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <button onclick="window.location.href='landingPage.html'" class="btn">Back to Home</button>
    </div>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
