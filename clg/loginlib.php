<?php
session_start();
require 'config.php'; 
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $usertype = $_POST['usertype']; // Retrieve usertype from hidden field

    // Create connection
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Determine which table to query based on usertype
    $table_name = ($usertype == 'librarian') ? 'librarian' : '';
    if (empty($table_name)) {
        echo "<script>alert('Invalid usertype'); window.location.href='librarian_login.html';</script>";
        exit();
    }

    // Check if username exists
    $sql = "SELECT * FROM $table_name WHERE username=?  ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Invalid username'); window.location.href='librarian_login.html';</script>";
    } else {
        $row = $result->fetch_assoc();
        if ($row['password'] == $password) {
            // Check if the user already exists in `users` table
            $sql_check_user = "SELECT * FROM users WHERE username=?";
            $stmt_check_user = $conn->prepare($sql_check_user);
            if (!$stmt_check_user) {
                die("Prepare statement failed: " . $conn->error);
            }
            $stmt_check_user->bind_param("s", $username);
            $stmt_check_user->execute();
            $result_check_user = $stmt_check_user->get_result();

            $login_time = date("Y-m-d H:i:s");
            if ($result_check_user->num_rows > 0) {
                // Update login time if user already exists in `users` table
                $sql_update_time = "UPDATE users SET time=? WHERE username=?";
                $stmt_update_time = $conn->prepare($sql_update_time);
                if (!$stmt_update_time) {
                    die("Prepare statement failed: " . $conn->error);
                }
                $stmt_update_time->bind_param("ss", $login_time, $username);
                $stmt_update_time->execute();
            } else {
                // Insert into `users` table if user doesn't exist
                $sql_insert_user = "INSERT INTO users (username, usertype, time) VALUES (?, ?, ?)";
                $stmt_insert_user = $conn->prepare($sql_insert_user);
                if (!$stmt_insert_user) {
                    die("Prepare statement failed: " . $conn->error);
                }
                $stmt_insert_user->bind_param("sss", $username, $usertype, $login_time);
                $stmt_insert_user->execute();
            }

            // Set session variables and redirect to libhome.html
            $_SESSION['username'] = $username;
            $_SESSION['usertype'] = $usertype;
            echo "<script>alert('Successfully logged in'); window.location.href='libhome.html';</script>";
        } else {
            echo "<script>alert('Incorrect password. Please retry!'); window.location.href='librarian_login.html';</script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
