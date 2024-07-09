<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $fileCount = $_POST['fileCount'];

    // Database connection details
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "fileuploaddownload";

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the faculty username exists in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Faculty username does not exist, show alert and exit
        echo "<script>alert('Invalid username'); window.location.href = 'index.html';</script>";
        exit;
    }

    // Proceed with file upload if faculty username exists
    if (isset($_FILES["file"]) && count($_FILES["file"]["name"]) > 0) {
        $total_files = count($_FILES["file"]["name"]);
        
        if ($total_files != $fileCount) {
            echo "The number of files uploaded does not match the specified count.";
            exit;
        }

        $target_dir = "uploads/"; 
        $allowed_types = array("jpg", "jpeg", "png", "gif", "pdf");
        
        // Start transaction
        $conn->begin_transaction();

        try {
            $upload_errors = [];

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES["file"]["error"][$i] == 0) {
                    $target_file = $target_dir . basename($_FILES["file"]["name"][$i]);
                    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check if the file type is allowed
                    if (!in_array($file_type, $allowed_types)) {
                        $upload_errors[] = "File " . $_FILES["file"]["name"][$i] . " has an invalid file type.";
                        continue;
                    }

                    // Move the uploaded file to the specified directory
                    if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], $target_file)) {
                        // File upload success, now store information in the database
                        $filename = $_FILES["file"]["name"][$i];
                        $filesize = $_FILES["file"]["size"][$i];
                        $filetype = $_FILES["file"]["type"][$i];
                        $upload_time = date("Y-m-d H:i:s");

                        // Insert the file information into the database
                        $stmt = $conn->prepare("INSERT INTO uploads (username, filename, filesize, filetype, upload_time) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssiss", $username, $filename, $filesize, $filetype, $upload_time);

                        if (!$stmt->execute()) {
                            $upload_errors[] = "Error storing information for file " . $filename . " in the database: " . $stmt->error;
                        }

                        $stmt->close();
                    } else {
                        $upload_errors[] = "There was an error uploading file " . $_FILES["file"]["name"][$i];
                    }
                } else {
                    $upload_errors[] = "No file was uploaded or there was an error with file " . $_FILES["file"]["name"][$i];
                }
            }

            if (empty($upload_errors)) {
                // Commit transaction
                $conn->commit();
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
