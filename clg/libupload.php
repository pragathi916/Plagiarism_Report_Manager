<?php
session_start();
require 'config.php'; // Ensure this file correctly sets up $conn
date_default_timezone_set('Asia/Kolkata');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please log in first.'); window.location.href = 'login.html';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_name = $_POST['recipient_name'];
    $recipient_email = $_POST['recipient_email'];
    $max_files = $_POST['max_files'];

    // Validate the recipient's information
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $recipient_name, $recipient_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('No matching faculty member found.'); window.history.back();</script>";
        exit;
    }

    // Validate the logged-in user
    $logged_in_user = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $logged_in_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Logged-in user not found.'); window.history.back();</script>";
        exit;
    }

    // Proceed with file upload
    if (isset($_FILES["document"]) && count($_FILES["document"]["name"]) > 0) {
        $total_files = count($_FILES["document"]["name"]);

        if ($total_files != $max_files) {
            echo "<script>alert('The number of files uploaded does not match the specified count.'); window.history.back();</script>";
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            $upload_errors = [];

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES["document"]["error"][$i] == 0) {
                    $filename = basename($_FILES["document"]["name"][$i]);
                    $filesize = $_FILES["document"]["size"][$i];
                    $filetype = $_FILES["document"]["type"][$i];
                    $filecontent = file_get_contents($_FILES["document"]["tmp_name"][$i]);
                    $upload_time = date("Y-m-d H:i:s");

                    // Insert the file information into the database
                    $stmt = $conn->prepare("INSERT INTO libupload (faculty_username, email, filename, filesize, filetype, filecontent, upload_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $recipient_name, $recipient_email, $filename, $filesize, $filetype, $filecontent, $upload_time);

                    if (!$stmt->execute()) {
                        $upload_errors[] = "Error storing information for file " . $filename . " in the database: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $upload_errors[] = "Error with file " . $_FILES["document"]["name"][$i];
                }
            }

            if (empty($upload_errors)) {
                // Commit transaction
                $conn->commit();
                echo "<script>alert('Files uploaded successfully!'); window.location.href = 'libhome.html';</script>";
            } else {
                // Rollback transaction
                $conn->rollback();
                foreach ($upload_errors as $error) {
                    echo "<script>alert('$error'); window.history.back();</script>";
                }
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "<script>alert('An error occurred: " . $e->getMessage() . "'); window.history.back();</script>";
        }

        $conn->close();
    } else {
        echo "<script>alert('No files were uploaded.'); window.history.back();</script>";
    }
}
?>
