<?php
require 'mailer.php'; // to load your PHPMailer config
require 'config.php';
date_default_timezone_set('Asia/Kolkata');
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $department = $_POST['department'];
    $faculty_id = $_POST['faculty_id'];
    $email = $_POST['email'];
    $fileCount = $_POST['fileCount'];

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the logged-in username matches the form's username
    if ($_SESSION['username'] !== $username) {
        echo "<script>alert('You can only upload files for your own username.'); window.location.href = 'faculty_index.html';</script>";
        exit;
    }

    // Check if the faculty details match
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE username = ? AND department = ? AND faculty_id = ? AND email = ?");
    $stmt->bind_param("ssss", $username, $department, $faculty_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Faculty details do not match our records.'); window.location.href = 'facultyindex.html';</script>";
        exit;
    }

    // Proceed with file upload
    if (isset($_FILES["file"]) && count($_FILES["file"]["name"]) > 0) {
        $total_files = count($_FILES["file"]["name"]);

        if ($total_files != $fileCount) {
            echo "The number of files uploaded does not match the specified count.";
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            $upload_errors = [];

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES["file"]["error"][$i] == 0) {
                    $filename = $_FILES["file"]["name"][$i];
                    $filesize = $_FILES["file"]["size"][$i];
                    $filetype = $_FILES["file"]["type"][$i];
                    $filecontent = file_get_contents($_FILES["file"]["tmp_name"][$i]);
                    $upload_time = date("Y-m-d H:i:s");

                    // Insert the file information into the database
                    $stmt = $conn->prepare("INSERT INTO uploads (username, filename, filesize, filetype, filecontent, upload_time) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisss", $username, $filename, $filesize, $filetype, $filecontent, $upload_time);

                    if (!$stmt->execute()) {
                        $upload_errors[] = "Error storing information for file " . $filename . " in the database: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $upload_errors[] = "No file was uploaded or there was an error with file " . $_FILES["file"]["name"][$i];
                }
            }

            if (empty($upload_errors)) {
                // Commit transaction
                $conn->commit();

                // Send email notification to all librarians
                $mail = require 'mailer.php';

                // Fetch librarian emails
                $librarianEmails = [];
                $librarianQuery = "SELECT email FROM librarian";
                $librarianResult = $conn->query($librarianQuery);
                while ($row = $librarianResult->fetch_assoc()) {
                    $librarianEmails[] = $row['email'];
                }

                try {
                    $mail->setFrom('nmamitcollege@gmail.com', 'Academic Integrity Portal');

                    foreach ($librarianEmails as $libEmail) {
                        $mail->addBCC($libEmail);
                    }

                    $mail->Subject = "New Upload by $username";
                    $mail->isHTML(true);
                    $mail->Body = "Faculty <strong>$username</strong> (<a href='mailto:$email'>$email</a>) from <strong>$department</strong> has uploaded <strong>$fileCount</strong> document(s) for plagiarism check.";

                    // Attach uploaded files
                    for ($i = 0; $i < $total_files; $i++) {
                        $mail->addAttachment($_FILES['file']['tmp_name'][$i], $_FILES['file']['name'][$i]);
                    }

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mail Error: " . $mail->ErrorInfo);
                }

                echo "<script>alert('Form submitted successfully!'); window.location.href = 'home.html';</script>";
            } else {
                // Rollback transaction
                $conn->rollback();
                foreach ($upload_errors as $error) {
                    echo $error . "<br>";
                }
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "An error occurred: " . $e->getMessage();
        }

        $conn->close();
    } else {
        echo "No files were uploaded.";
    }
}
?>
