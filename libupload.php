<?php
require 'mailer.php';
session_start();
$conn = require 'config.php';
date_default_timezone_set('Asia/Kolkata');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please log in first.'); window.location.href = 'index.html';</script>";
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

                    $stmt = $conn->prepare("INSERT INTO libupload (faculty_username, email, filename, filesize, filetype, filecontent, upload_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $recipient_name, $recipient_email, $filename, $filesize, $filetype, $filecontent, $upload_time);

                    if (!$stmt->execute()) {
                        $upload_errors[] = "Error storing file: $filename – " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $upload_errors[] = "Error with file: " . $_FILES["document"]["name"][$i];
                }
            }

            if (empty($upload_errors)) {
                $conn->commit();

                // Send email to the faculty
                $mail = require 'mailer.php';

                try {
                    $mail->setFrom('nmamitcollege@gmail.com', 'Plagiarism Report System');
                    $mail->addAddress($recipient_email);
                    $mail->Subject = "Plagiarism Report Uploaded";

                    $mail->isHTML(true);
                    $mail->Body = "Dear faculty <strong>$recipient_name</strong>,<br><br>
                        <strong>$logged_in_user</strong> (librarian) has uploaded a plagiarism check report for the document(s) you had submitted.<br><br>
                        Please find the attached report(s).<br><br>
                        Regards,<br>Plagiarism Team";

                    // Attach all uploaded files
                    for ($i = 0; $i < $total_files; $i++) {
                        $mail->addAttachment($_FILES["document"]["tmp_name"][$i], $_FILES["document"]["name"][$i]);
                    }

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email sending failed: " . $mail->ErrorInfo);
                }

                echo "<script>alert('Files uploaded and email sent to faculty.'); window.location.href = 'libhome.html';</script>";
            } else {
                $conn->rollback();
                foreach ($upload_errors as $error) {
                    echo "<script>alert('$error'); window.history.back();</script>";
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('An error occurred: " . $e->getMessage() . "'); window.history.back();</script>";
        }

        $conn->close();
    } else {
        echo "<script>alert('No files were uploaded.'); window.history.back();</script>";
    }
}
?>
