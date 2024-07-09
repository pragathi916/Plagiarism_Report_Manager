<?php
session_start();
require 'config.php';

// Check if the user is already logged in
if (isset($_SESSION['username']) && isset($_SESSION['usertype'])) {
    echo "<script>alert('You are already logged in'); window.location.href='home.html';</script>";
    exit();
}

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
    $table_name = ($usertype == 'faculty') ? 'faculty' : '';

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
            // Save login time and usertype
            $login_time = date("Y-m-d H:i:s");

            $sql = "INSERT INTO users (username, usertype, time) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $usertype, $login_time);
            $stmt->execute();

            // Set session variables and redirect to home.html
            $_SESSION['username'] = $username;
            $_SESSION['usertype'] = $usertype;
            echo "<script>alert('Successfully logged in'); window.location.href='home.html';</script>";
        } else {
            echo "<script>alert('Incorrect password. Please retry!'); window.location.href='faculty_login.html';</script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
