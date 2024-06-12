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

    // Fetch note details from database based on sno
    $sql = "SELECT * FROM user_notes WHERE sno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sno);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $noteTitle = $row['title'];
        $noteText = $row['note'];

        // Close statement
        $stmt->close();
    } else {
        echo "Note not found";
    }
} else {
    echo "Invalid request";
    exit();
}
?>

<!-- HTML form to edit note -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                Edit Note
            </div>
            <div class="card-body">
                <form action="update_note.php" method="POST">
                    <input type="hidden" name="sno" value="<?php echo $sno; ?>">
                    <div class="form-group">
                        <label for="noteTitle">Title</label>
                        <input type="text" class="form-control" id="noteTitle" name="noteTitle" value="<?php echo $noteTitle; ?>">
                    </div>
                    <div class="form-group">
                        <label for="noteText">Note</label>
                        <textarea class="form-control" id="noteText" name="noteText" rows="3"><?php echo $noteText; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Note</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
