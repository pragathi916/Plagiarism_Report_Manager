<?php
session_start();
require 'config.php';
date_default_timezone_set('Asia/Kolkata');

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'admin'; 
    $login_time = date("Y-m-d H:i:s");

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username exists in admin table
    $sql = "SELECT * FROM admin WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Invalid username'); window.location.href='admin_login.html';</script>";
        exit();
    }

    $row = $result->fetch_assoc();
    if ($row['password'] !== $password) {
        echo "<script>alert('Incorrect password. Please retry!'); window.location.href='admin_login.html';</script>";
        exit();
    }

    // If password matches, set session
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;

    // Check if user exists in `users` table
    $sql_check_user = "SELECT * FROM users WHERE username=?";
    $stmt_check_user = $conn->prepare($sql_check_user);
    $stmt_check_user->bind_param("s", $username);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();

    if ($result_check_user->num_rows > 0) {
        // Update login time
        $sql_update_time = "UPDATE users SET time=? WHERE username=?";
        $stmt_update_time = $conn->prepare($sql_update_time);
        $stmt_update_time->bind_param("ss", $login_time, $username);
        $stmt_update_time->execute();
    } else {
        // Insert into users table
        $sql_insert_user = "INSERT INTO users (username, role, time) VALUES (?, ?, ?)";
        $stmt_insert_user = $conn->prepare($sql_insert_user);
        $stmt_insert_user->bind_param("sss", $username, $role, $login_time);
        $stmt_insert_user->execute();
    }

    // Redirect to admin page
    echo "<script>alert('Successfully logged in'); window.location.href='admin.html';</script>";

    // Close all prepared statements and connection
    $stmt->close();
    $conn->close();
} else {
    // If not POST request, redirect to login
    header("Location: admin_login.html");
    exit();
}
?>
