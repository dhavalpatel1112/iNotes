<?php
// Database connection parameters
$servername = "localhost";  // Replace with your MySQL server name
$username = "root";    // Replace with your MySQL username
$password = "";    // Replace with your MySQL password
$dbname = "inote";         // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sno = $_POST['sno'];
    $title = $_POST['noteTitle'];
    $note = $_POST['noteText'];

    // Update note in database
    $sql = "UPDATE user_notes SET title = ?, note = ? WHERE sno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $note, $sno);
    
    if ($stmt->execute()) {
        // Redirect to the main page after successful update
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating note: " . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>
