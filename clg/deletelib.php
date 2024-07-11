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
    $delete_username = $_POST['username'];


    // Then delete from librarian table
    $sql_delete_librarian = "DELETE FROM librarian WHERE username=?";
    $stmt_delete_librarian = $conn->prepare($sql_delete_librarian);
    $stmt_delete_librarian->bind_param("s", $delete_username);
    $stmt_delete_librarian->execute();

    if ($stmt_delete_librarian->affected_rows > 0) {
        // If deleted from librarian table, delete from users table if exists
        $sql_delete_users = "DELETE FROM users WHERE username=?";
        $stmt_delete_users = $conn->prepare($sql_delete_users);
        $stmt_delete_users->bind_param("s", $delete_username);
        $stmt_delete_users->execute();
        $stmt_delete_users->close();

        echo "<script>alert('Librarian deleted successfully.'); window.location.href='admin.html';</script>";
    } else {
        echo "<script>alert('Librarian not found.'); window.history.back();</script>";
    }

    $stmt_delete_librarian->close();
}

$conn->close();
?>
