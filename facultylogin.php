<?php
session_start();
require 'config.php';
date_default_timezone_set('Asia/Kolkata');

// Check if the user is already logged in
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    // Check if the session user matches the user logging in
    if ($_SESSION['username'] !== $_POST['username']) {
        // Different user is attempting to login; do not update time
        echo "<script>alert('Another user is already logged in. Please log out first.'); window.location.href='faculty_login.html';</script>";
        exit();
    }
    
    // Update login time in `users` table for the current user
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_SESSION['username'];
    $login_time = date("Y-m-d H:i:s");

    $sql_update_time = "UPDATE users SET time=? WHERE username=?";
    $stmt_update_time = $conn->prepare($sql_update_time);
    $stmt_update_time->bind_param("ss", $login_time, $username);
    $stmt_update_time->execute();

    // Redirect to home page
    echo "<script>alert('You are already logged in. Login time updated.'); window.location.href='home.html';</script>";
    exit();
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = "faculty";
    $table_name='faculty';
    // Create connection
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username exists
    $sql = "SELECT * FROM $table_name WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Invalid username'); window.location.href='faculty_login.html';</script>";
    } else {
        $row = $result->fetch_assoc();
        if ($row['password'] == $password) { // Assuming passwords are stored as plain text, consider hashing
            // Set session variables
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Check if the user already exists in `users` table
            $sql_check_user = "SELECT * FROM users WHERE username=?";
            $stmt_check_user = $conn->prepare($sql_check_user);
            $stmt_check_user->bind_param("s", $username);
            $stmt_check_user->execute();
            $result_check_user = $stmt_check_user->get_result();

            if ($result_check_user->num_rows > 0) {
                // Update login time if user already exists in `users` table
                $sql_update_time = "UPDATE users SET time=? WHERE username=?";
                $stmt_update_time = $conn->prepare($sql_update_time);
                $login_time = date("Y-m-d H:i:s");
                $stmt_update_time->bind_param("ss", $login_time, $username);
                $stmt_update_time->execute();
            } else {
                // Insert into `users` table if user doesn't exist
                $sql_insert_user = "INSERT INTO users (username, role, time) VALUES (?, ?, ?)";
                $stmt_insert_user = $conn->prepare($sql_insert_user);
                $login_time = date("Y-m-d H:i:s");
                $stmt_insert_user->bind_param("sss", $username, $role, $login_time);
                $stmt_insert_user->execute();
            }

            // Redirect to home.html
            echo "<script>alert('Successfully logged in'); window.location.href='home.html';</script>";
        } else {
            echo "<script>alert('Incorrect password. Please retry!'); window.location.href='faculty_login.html';</script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
