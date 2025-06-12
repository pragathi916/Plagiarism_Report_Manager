<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    die("Unauthorized access. Please log in.");
}

$faculty_username = $_SESSION['username'];

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch files uploaded *to* the currently logged-in faculty
$sql = "SELECT * FROM libupload WHERE faculty_username = ? ORDER BY upload_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $faculty_username);
$stmt->execute();
$result = $stmt->get_result();
$total_files_uploaded = $result->num_rows;

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reports Shared With You</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function returnBack() {
            window.location.href = "home.html";
        }
    </script>
</head>
<body>
<div class="main-content">
    <div class="nav">
        <img id="logo" src="nitte.png" alt="Nitte Logo">
        <p id="head">Academic Integrity Portal</p>
        <button class="logout-button" type="button" onclick="returnBack()">Return Back</button>
    </div>

    <div class="container mt-5">
        <h2>Reports Shared With You</h2>
        <p>Total files received: <?php echo $total_files_uploaded; ?></p>

        <table class="file-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>File Type</th>
                    <th>Upload Time</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['filename']); ?></td>
                            <td><?php echo htmlspecialchars($row['filesize']); ?> bytes</td>
                            <td><?php echo htmlspecialchars($row['filetype']); ?></td>
                            <td><?php echo htmlspecialchars($row['upload_time']); ?></td>
                            <td>
                                <a href="downloadlibfile.php?id=<?php echo htmlspecialchars($row['id']); ?>">Download</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6'>No files shared with you yet.</td></tr>";
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
