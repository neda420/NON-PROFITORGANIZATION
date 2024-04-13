<?php
session_start();

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

// Validate Admin Credentials
$valid_username = "admin";
$valid_password = "admin";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
    $entered_username = $_POST["username"];
    $entered_password = $_POST["password"];

    if ($entered_username != $valid_username || $entered_password != $valid_password) {
        die("Invalid username or password");
    }
}

// Handle Update
$updateMessage = "";  // Variable to store the update message

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_id"])) {
    $update_id = $_POST["update_id"];
    $new_name = $_POST["new_name"];
    $new_email = $_POST["new_email"];
    $new_message = $_POST["new_message"];

    // Using prepared statements to prevent SQL injection
    $stmt = $conn->prepare("UPDATE contact_messages SET name=?, email=?, message=? WHERE id=?");
    $stmt->bind_param("sssi", $new_name, $new_email, $new_message, $update_id);

    if ($stmt->execute()) {
        $updateMessage = "Record updated successfully";
    } else {
        $updateMessage = "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch contact messages from database
$sql = "SELECT id, name, email, message, created_at FROM contact_messages";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="adminStyles.css">
</head>
<body>
    <div class="admin-container">
        <h1>Contact Messages</h1>
        
        <!-- Home Button -->
        <a href="landingPage.html" class="home-button">HOME</a>

        <!-- Display Update Message -->
        <?php if (!empty($updateMessage)): ?>
            <p class="update-message"><?php echo $updateMessage; ?></p>
        <?php endif; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            <?php
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["name"] . "</td>
                        <td>" . $row["email"] . "</td>
                        <td>" . $row["message"] . "</td>
                        <td>" . $row["created_at"] . "</td>
                        <td>
                            <a href='#' onclick='editRow(\"" . $row["id"] . "\", \"" . $row["name"] . "\", \"" . $row["email"] . "\", \"" . $row["message"] . "\")'>Edit</a> |
                            <a href='delete.php?id=" . $row["id"] . "' onclick='return confirm(\"Are you sure you want to delete this record?\")'>Delete</a>
                        </td>
                    </tr>";
            }
            ?>
        </table>

        <!-- Edit Form -->
        <form id="editForm" action="" method="post" style="display: none;">
            <input type="hidden" id="update_id" name="update_id">
            <input type="text" id="new_name" name="new_name" placeholder="New Name" required>
            <input type="email" id="new_email" name="new_email" placeholder="New Email" required>
            <textarea id="new_message" name="new_message" placeholder="New Message" required></textarea>
            <button type="submit">Update</button>
        </form>

        <a href="#" class="logout-button" onclick="logout()">Logout</a>

        <script>
            function editRow(id, name, email, message) {
                document.getElementById("update_id").value = id;
                document.getElementById("new_name").value = name;
                document.getElementById("new_email").value = email;
                document.getElementById("new_message").value = message;
                
                document.getElementById("editForm").style.display = "block";
            }

            function logout() {
                window.location.href = 'admin_login.html';
            }
        </script>
    </div>
</body>
</html>

<?php
$conn->close();
?>
