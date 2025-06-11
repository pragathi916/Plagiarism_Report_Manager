<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uploaded Files</title>
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
        <h2>Uploaded Files</h2>

        <?php
        session_start();
        require 'config.php'; 
        if (isset($_SESSION['username'])) {
            $loggedInUsername = $_SESSION['username']; // Assuming session contains logged in username
           
        $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

            // Fetch the uploaded files by the logged-in user from the database
            $stmt = $conn->prepare("SELECT * FROM uploads WHERE username = ?");
            $stmt->bind_param("s", $loggedInUsername);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_files_uploaded = $result->num_rows;

            echo "<p>Total files uploaded: " . $total_files_uploaded . "</p>";
        } else {
            echo "<p>Please log in to view uploaded files.</p>";
        }
        ?>

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
                if (isset($result) && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['filename']); ?></td>
                            <td><?php echo htmlspecialchars($row['filesize']); ?> bytes</td>
                            <td><?php echo htmlspecialchars($row['filetype']); ?></td>
                            <td><?php echo htmlspecialchars($row['upload_time']); ?></td>
                            <td>
    <a href="download.php?id=<?php echo htmlspecialchars($row['upload_id']); ?>">Download</a>
</td>

                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5">No files uploaded yet.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<footer>
    <p>Copyright &copy; 2024</p>
    NMAM Institute Of Technology, Nitte
</footer>

</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>
