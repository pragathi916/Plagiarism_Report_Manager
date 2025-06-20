<?php
session_start();
require 'config.php';

// Get logged-in faculty username
$loggedInUsername = $_SESSION['username'] ?? null;
if (!$loggedInUsername) {
    die("You must be logged in to download files.");
}

// DB connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $file_id = intval($_GET['id']); // Sanitize input

    // Fetch file only if it was sent to the logged-in faculty
    $stmt = $conn->prepare("SELECT filename, filecontent, filetype FROM libupload WHERE id = ? AND faculty_username = ?");
    $stmt->bind_param("is", $file_id, $loggedInUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $filename = $row['filename'];
        $filecontent = $row['filecontent'];
        $filetype = $row['filetype'];

        // Force download headers
        header("Content-Description: File Transfer");
        header("Content-Type: " . $filetype);
        header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . strlen($filecontent));

        echo $filecontent;
        exit;
    } else {
        echo "File not found or permission denied.";
    }

    $stmt->close();
} else {
    echo "Invalid file ID.";
}

$conn->close();
?>
