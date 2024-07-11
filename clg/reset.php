<?php
session_start();
require 'config.php'; // Include your database configuration

// Check if the user is logged in as admin
if (!isset($_SESSION['username'])) {
    // Redirect to login page with an alert message
    echo "<script>alert('Please login first.'); window.location.href='login.html';</script>";
    exit();
}

// Verify if the logged-in user exists in the users table
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];
$sql_check_user = "SELECT * FROM users WHERE username=?";
$stmt_check_user = $conn->prepare($sql_check_user);
$stmt_check_user->bind_param("s", $username);
$stmt_check_user->execute();
$result_check_user = $stmt_check_user->get_result();

if ($result_check_user->num_rows == 0) {
    // User does not exist in users table, redirect to login page
    echo "<script>alert('Please login first.'); window.location.href='login.html';</script>";
    exit();
}

$stmt_check_user->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $reset_username = $_POST['username'];
    $new_password = $_POST['password'];

    // Check if username exists in faculty table
    $sql_check_faculty = "SELECT * FROM faculty WHERE username=?";
    $stmt_check_faculty = $conn->prepare($sql_check_faculty);
    $stmt_check_faculty->bind_param("s", $reset_username);
    $stmt_check_faculty->execute();
    $result_check_faculty = $stmt_check_faculty->get_result();

    if ($result_check_faculty->num_rows > 0) {
        // Username found in faculty table, update password
        $sql_update_faculty = "UPDATE faculty SET password=? WHERE username=?";
        $stmt_update_faculty = $conn->prepare($sql_update_faculty);
        $stmt_update_faculty->bind_param("ss", $new_password, $reset_username);
        $stmt_update_faculty->execute();
        $stmt_update_faculty->close();

        echo "<script>alert('Password reset successfully for faculty.'); window.location.href='admin.html';</script>";
    } else {
        // Check if username exists in librarian table
        $sql_check_librarian = "SELECT * FROM librarian WHERE username=?";
        $stmt_check_librarian = $conn->prepare($sql_check_librarian);
        $stmt_check_librarian->bind_param("s", $reset_username);
        $stmt_check_librarian->execute();
        $result_check_librarian = $stmt_check_librarian->get_result();

        if ($result_check_librarian->num_rows > 0) {
            // Username found in librarian table, update password
            $sql_update_librarian = "UPDATE librarian SET password=? WHERE username=?";
            $stmt_update_librarian = $conn->prepare($sql_update_librarian);
            $stmt_update_librarian->bind_param("ss", $new_password, $reset_username);
            $stmt_update_librarian->execute();
            $stmt_update_librarian->close();

            echo "<script>alert('Password reset successfully for librarian.'); window.location.href='admin.html';</script>";
        } else {
            // Username not found in either table
            echo "<script>alert('Username not found.'); window.history.back();</script>";
        }

        $stmt_check_librarian->close();
    }

    $stmt_check_faculty->close();
}

$conn->close();
?>
