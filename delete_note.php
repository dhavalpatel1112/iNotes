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

// Check if sno parameter is passed in URL
if (isset($_GET['sno'])) {
    $sno = $_GET['sno'];

    // Delete note from database based on sno
    $sql = "DELETE FROM user_notes WHERE sno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sno);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error: ' . $conn->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo 'invalid request';
}
?>
