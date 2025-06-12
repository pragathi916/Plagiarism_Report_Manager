<?php
session_start();
$loggedInUsername = $_SESSION['username'];
date_default_timezone_set('Asia/Kolkata');
// Initialize $search_username variable
$search_username = '';

// Database connection details
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "plag_check";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_username = $_POST['faculty_username'];

    // Prepare the SQL statement with a parameterized query
    $sql = "SELECT * FROM libupload WHERE faculty_username LIKE ? ORDER BY upload_time DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%{$search_username}%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default query without search
    $sql = "SELECT * FROM libupload ORDER BY upload_time DESC";
    $result = $conn->query($sql);
}

$total_files_uploaded = $result->num_rows;

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uploaded Files</title>
    <link rel="stylesheet" href="style.css">
    <script>
// Function to check if user is logged in
function checkLoggedIn() {
    // Check if session variable is not set
    <?php if (!isset($_SESSION['username'])): ?>
        alert("Please login first to access this page.");
        window.location.href = "login.html";
    <?php endif; ?>
}
</script>
    <script>
        function returnBack() {
            window.location.href = "libhome.html";
        }
    </script>
</head>
<body onload="checkLoggedIn();">

<div class="main-content">
    <div class="nav">
        <img id="logo" src="nitte.png" alt="Nitte Logo">
        <p id="head">Academic Integrity Portal</p>
        <button class="logout-button" type="button" onclick="returnBack()">Return Back</button>
    </div>
    <div class="container mt-5">
        <h2>Uploaded Files</h2>

        <!-- Search form -->
        <form method="POST">
            <label>Search:</label>
            <input type="text" id="username" name="faculty_username" placeholder="Search by Username" value="<?php echo htmlspecialchars($search_username); ?>">
            <button type="submit" name="search" class="sb">Search</button>
            <?php if (isset($_POST['search'])): ?>
                <input type="submit" name="showAll" value="Show All" class="sb">
            <?php endif; ?>
        </form>

        <?php
        echo "<p>Total files uploaded: " . $total_files_uploaded . "</p>";
        ?>

        <table class="file-table">
            <thead>
                <tr>
                    <th>Faculty Username</th>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>File Type</th>
                    <th>Upload Time</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the uploaded files and download links
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['faculty_username']); ?></td>
                            <td><?php echo htmlspecialchars($row['filename']); ?></td>
                            <td><?php echo htmlspecialchars($row['filesize']); ?> bytes</td>
                            <td><?php echo htmlspecialchars($row['filetype']); ?></td>
                            <td><?php echo htmlspecialchars($row['upload_time']); ?></td>
                            <td>
                                <a href="downloadreport.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-primary">Download</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6">No files uploaded yet.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<footer>
    <p>&copy; 2024 NMAM Institute Of Technology, Nitte</p>
</footer>

</body>
</html>
