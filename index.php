<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inote";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['noteTitle'];
    $note = $_POST['noteText'];
    
    // Prepare SQL statement
    $sql = "INSERT INTO user_notes (title, note) VALUES (?, ?)";
    
    // Use prepared statement for security and efficiency
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $title, $note);
    
    // Execute the statement
    if ($stmt->execute()) {
        // echo "New note added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    // Close statement and connection
    $stmt->close();
}

// Determine the number of entries to display per page
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;


// Search handling
if(isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $searchSql = "SELECT * FROM user_notes WHERE title LIKE ? LIMIT ?, ?";
    $searchStmt = $conn->prepare($searchSql);
    $searchStmt->bind_param("sii", $searchKeyword, $offset, $entriesPerPage);
    $searchStmt->execute();
    $result = $searchStmt->get_result();
} else {
    // Fetch notes from the database with pagination
    $sql = "SELECT * FROM user_notes LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $entriesPerPage);
    $stmt->execute();
    $result = $stmt->get_result();
}


// Get the total number of notes
$sqlTotal = "SELECT COUNT(*) AS total FROM user_notes";
$totalResult = $conn->query($sqlTotal);
$totalRow = $totalResult->fetch_assoc();
$totalEntries = $totalRow['total'];
$totalPages = ceil($totalEntries / $entriesPerPage);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iNote Project</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        /* Add custom CSS here if needed */
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">iNote</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="#">About</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Contact Us</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <!-- Add a Note Form -->
    <div class="card">
        <div class="card-header">
            Add a Note to iNote
        </div>
        <div class="card-body">
            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="noteTitle">Title</label>
                    <input type="text" class="form-control" id="noteTitle" name="noteTitle" placeholder="Enter note title">
                </div>
                <div class="form-group">
                    <label for="noteText">Note</label>
                    <textarea class="form-control" id="noteText" name="noteText" rows="3" placeholder="Enter your note"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Note</button>
            </form>
            
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <!-- Show entries dropdown -->
            <div class="form-group">
                <label for="entriesDropdown">Show entries:</label>
                <select class="form-control" id="entriesDropdown" onchange="updateEntries()">
                    <option value="10" <?php if($entriesPerPage == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if($entriesPerPage == 20) echo 'selected'; ?>>20</option>
                    <option value="50" <?php if($entriesPerPage == 50) echo 'selected'; ?>>50</option>
                    <option value="100" <?php if($entriesPerPage == 100) echo 'selected'; ?>>100</option>
                </select>
            </div>
        </div>
        <!-- Search input -->
        <div class="form-group float-right">
            <label for="searchInput">Search:</label>
            <form action="index.php" method="GET" class="form-inline">
                <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search notes">
                <button type="submit" class="btn btn-primary ml-2">Search</button>
            </form>
        </div>
    </div>
</div>

<div class="container mt-5 mb-5">
    <!-- Display Notes -->
    <div class="card">
        <div class="card-header">
            Your Notes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">S.No</th>
                            <th scope="col">Title</th>
                            <th scope="col">Description</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if ($result->num_rows > 0) {
                            $sno = $offset + 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $sno++ . "</td>";
                                echo "<td>" . $row["title"] . "</td>";
                                echo "<td>" . $row["note"] . "</td>";
                                echo '<td>
                                        <a href="edit_note.php?sno=' . $row["sno"] . '" class="btn btn-sm btn-primary">Edit</a>
                                        <button class="btn btn-sm btn-danger delete-btn" data-sno="' . $row["sno"] . '">Delete</button>
                                    </td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No notes found</td></tr>";
                        }
                        ?>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php
                    for ($i = 1; $i <= $totalPages; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                                <a class="page-link" href="index.php?page=' . $i . '&entries=' . $entriesPerPage . '">' . $i . '</a>
                              </li>';
                    }
                    ?>
                </ul>
            </nav>
            <!-- Display range of entries -->
            <div class="text-center mt-3 text-success">
                Displaying <?php echo min($offset + 1, $totalEntries); ?> to <?php echo min($offset + $entriesPerPage, $totalEntries); ?> of <?php echo $totalEntries; ?> entries.
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this note?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateEntries() {
        const entries = document.getElementById('entriesDropdown').value;
        window.location.href = `index.php?entries=${entries}&page=1`;
    }

    document.addEventListener("DOMContentLoaded", function() {
        let snoToDelete = null;

        // Attach click event listener to delete buttons
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                snoToDelete = this.getAttribute('data-sno');
                $('#deleteModal').modal('show');
            });
        });

        // Handle the confirmation button click
        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (snoToDelete) {
                // Send a request to delete the note
                fetch('delete_note.php?sno=' + snoToDelete, {
                    method: 'GET'
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        // Reload the page to reflect changes
                        location.reload();
                    } else {
                        alert('Failed to delete the note.');
                    }
                });
            }
        });
    });
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

