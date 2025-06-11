<?php
session_start();
require 'config.php'; 

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please login first.'); window.location.href='login.html';</script>";
    exit();
}

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];
$sql_check_admin = "SELECT * FROM users WHERE username=? AND role='admin'";
$stmt_check_admin = $conn->prepare($sql_check_admin);
$stmt_check_admin->bind_param("s", $username);
$stmt_check_admin->execute();
$result_check_admin = $stmt_check_admin->get_result();

if ($result_check_admin->num_rows == 0) {
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
