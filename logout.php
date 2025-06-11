<?php
// Start session
session_start();

// Check if user is logged in
if (isset($_SESSION['username'])) {

$conn = require 'config.php';
require_once 'config.php'; 

    $username = $_SESSION['username'];

    // Debug log (optional)
    error_log("Attempting to delete user: $username");

    // Check if username exists
    $check_query = "SELECT username FROM users WHERE username = ?";
    $stmt_check = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt_check, "s", $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    $num_rows = mysqli_stmt_num_rows($stmt_check);
    mysqli_stmt_close($stmt_check);

    if ($num_rows > 0) {
        // Delete user record
        $delete_query = "DELETE FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Destroy session
        session_unset();
        session_destroy();

        echo "<script>alert('Logged Out. Redirecting to homepage.');</script>";
        echo '<meta http-equiv="refresh" content="2;url=index.html">';
        exit();
    } else {
        echo "<script>alert('User not found in database.');</script>";
        echo '<meta http-equiv="refresh" content="0;url=index.html">';
        exit();
    }

} else {
    // User not logged in
    echo "<script>alert('Please log in first.');</script>";
    echo '<meta http-equiv="refresh" content="0;url=index.html">';
    exit();
}
?>
