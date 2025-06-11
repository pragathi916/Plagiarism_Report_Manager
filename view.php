<?php
session_start();
require 'config.php';
$loggedInUsername = $_SESSION['username'];
$search_username = '';

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_username = $_POST['username'];
    $sql = "SELECT * FROM uploads WHERE username LIKE '%$search_username%' ORDER BY upload_time DESC";
} else {
    $sql = "SELECT * FROM uploads ORDER BY upload_time DESC";
}

$result = $conn->query($sql);
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
        function returnBack() {
            window.location.href = "libhome.html";
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
        <h2>Uploaded Files</h2>

        <!-- Search form -->
        <form method="POST">
            <label>Search:</label>
            <input type="text" id="username" name="username" placeholder="Search by Username" value="<?php echo htmlspecialchars($search_username); ?>">
            <button type="submit" name="search" class="sb">Search</button>
            <?php if (isset($_POST['search'])): ?>
                <button type="submit" name="showAll" class="sb">Show All</button>
            <?php endif; ?>
        </form>

        <?php
        echo "<p>Total files uploaded: " . $total_files_uploaded . "</p>";
        ?>

        <table class="file-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>File Name</th>
                    <th>File Size</th>
                    <th>File Type</th>
                    <th>Upload Time</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Example of generating HTML table rows with download links
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['filename']); ?></td>
                            <td><?php echo htmlspecialchars($row['filesize']); ?> bytes</td>
                            <td><?php echo htmlspecialchars($row['filetype']); ?></td>
                            <td><?php echo htmlspecialchars($row['upload_time']); ?></td>
                            <td>
                                    <a href="downloadall.php?id=<?php echo htmlspecialchars($row['upload_id']); ?>">Download</a>
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
