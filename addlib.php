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
    echo "<script>alert('You do not have permission to add a librarian.'); window.location.href='login.html';</script>";
    exit();
}

$stmt_check_admin->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $faculty_id = $_POST['faculty_id'];

    // Check if username already exists
$sql_check_username = "SELECT * FROM librarian WHERE username=?";
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
$sql_check_email = "SELECT * FROM librarian WHERE email=?";
$stmt_check_email = $conn->prepare($sql_check_email);
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result_check_email = $stmt_check_email->get_result();

if ($result_check_email->num_rows > 0) {
    echo "<script>alert('Email already exists. Please choose a different email.'); window.history.back();</script>";
    exit();
}

$stmt_check_email->close();

// Check if faculty ID already exists
$sql_check_faculty_id = "SELECT * FROM librarian WHERE faculty_id=?";
$stmt_check_faculty_id = $conn->prepare($sql_check_faculty_id);
$stmt_check_faculty_id->bind_param("s", $faculty_id);
$stmt_check_faculty_id->execute();
$result_check_faculty_id = $stmt_check_faculty_id->get_result();

if ($result_check_faculty_id->num_rows > 0) {
    echo "<script>alert('Faculty ID already exists. Please choose a different Faculty ID.'); window.history.back();</script>";
    exit();
}

$stmt_check_faculty_id->close();

    // Prepare SQL statement to insert data into librarian table
    $sql = "INSERT INTO librarian (username, password, email, faculty_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $email, $faculty_id);

    // Execute SQL statement
    if ($stmt->execute()) {
        echo "<script>alert('Librarian added successfully.'); window.location.href='admin.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
