<?php
session_start();
$loggedInUsername = $_SESSION['username'];

// Initialize $search_username variable
$search_username = '';

// Database connection details
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "fileuploaddownload";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_username = $_POST['username'];

    // Modify SQL query to include username filter
    $sql = "SELECT * FROM uploads WHERE username LIKE '%$search_username%' ORDER BY upload_time DESC";
} else {
    // Default query without search
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

    <style>
        /* Additional styles specific to this page can be added here */
        .file-table {
            color: black;
            margin: 50px;
            border-collapse: collapse;
            text-align: center;
            width: 85%;
            background-color: white; /* Set white background */
        }
        .file-table th, .file-table td {
            border: 1px solid black; /* Ensure table has borders */
            padding: 10px;
        }
        .file-table th {
            background-color: #39b3e6;
            color: black;
        }

        /* Ensure footer stays at the bottom */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        footer {
            margin-top: auto; /* Push footer to the bottom */
            text-align: center;
            background-color: white;
            color: #39b3e6; /* Blue color for text */
            padding: 10px 0;
            width: 100%;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            border-top: 2px solid #39b3e6;
        }
        .sb {
    margin: 10px;
    padding: 5px 10px; 
    border: none;
    background-color: white;
    color: #39b3e6;
    border-radius: 50px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    width: 89px; /* Adjust width as needed */
}
    </style>
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
                                <a href="downloadall.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Download</a>
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
