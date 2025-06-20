<?php
session_start();
require 'config.php';

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (isset($_GET['id'])) {
    $file_id = $_GET['id'];

    // Fetch the file details from the database
    $stmt = $conn->prepare("SELECT * FROM libupload WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $filename = $row['filename'];
        $filecontent = $row['filecontent'];
        $filetype = $row['filetype'];

        // Set headers for file download
        header("Content-Description: File Transfer");
        header("Content-Type: $filetype");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: " . strlen($filecontent));
        echo $filecontent;
    } else {
        echo "File not found or invalid file ID.";
    }

    $stmt->close();
} else {
    echo "Invalid file ID.";
}

$conn->close();
?>
