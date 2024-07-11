<?php
session_start();
require 'config.php'; // Include your database configuration

// Check if the user is logged in as admin
if (!isset($_SESSION['username'])) {
    // Redirect to login page with an alert message
    echo "<script>alert('Please login first.'); window.location.href='login.html';</script>";
    exit();
}

// Verify if the logged-in user is an admin using the users table
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];
$sql_check_admin = "SELECT * FROM users WHERE username=? AND usertype='admin'";
$stmt_check_admin = $conn->prepare($sql_check_admin);
$stmt_check_admin->bind_param("s", $username);
$stmt_check_admin->execute();
$result_check_admin = $stmt_check_admin->get_result();

if ($result_check_admin->num_rows == 0) {
    // Not an admin, redirect to login page
    echo "<script>alert('You do not have permission to add faculty.'); window.location.href='login.html';</script>";
    exit();
}

$stmt_check_admin->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $department = $_POST['department'];
    $faculty_id = $_POST['faculty_id'];
    $email = $_POST['email'];

    // Validate and sanitize inputs (consider using proper validation/sanitization methods)

    // Check if username already exists
    $sql_check_username = "SELECT * FROM faculty WHERE username=?";
    $stmt_check_username = $conn->prepare($sql_check_username);
    $stmt_check_username->bind_param("s", $username);
    $stmt_check_username->execute();
    $result_check_username = $stmt_check_username->get_result();

    if ($result_check_username->num_rows > 0) {
        echo "<script>alert('Username already exists. Please choose a different username.'); window.history.back();</script>";
        exit();
    }

    $stmt_check_username->close();

    // Check if email already exists
    $sql_check_email = "SELECT * FROM faculty WHERE email=?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_email->num_rows > 0) {
        echo "<script>alert('Email already exists. Please choose a different email.'); window.history.back();</script>";
        exit();
    }

    $stmt_check_email->close();

    // Prepare SQL statement to insert data into faculty table
    $sql = "INSERT INTO faculty (username, password, department, faculty_id, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password, $department, $faculty_id, $email);

    // Execute SQL statement
    if ($stmt->execute()) {
        echo "<script>alert('Faculty added successfully.'); window.location.href='admin.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
