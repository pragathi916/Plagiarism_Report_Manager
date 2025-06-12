<?php
session_start();
$conn=require 'config.php';
$error = "";

// If confirmed via GET
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes' && isset($_GET['user'])) {
    $username = $_GET['user'];

    // Start transaction
    $conn->begin_transaction();

    try {
        $stmt1 = $conn->prepare("DELETE FROM librarian WHERE username = ?");
        $stmt1->bind_param("s", $username);
        $stmt1->execute();

        // Delete from users
        $stmt2 = $conn->prepare("DELETE FROM users WHERE username = ?");
        $stmt2->bind_param("s", $username);
        $stmt2->execute();

        $conn->commit();
        echo "<script>alert('Account deleted successfully.'); window.location.href = 'index.html';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Failed to delete account.'); window.location.href = 'libhome.html';</script>";
    }
    exit;
}

// On form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM librarian WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $error = "Username not found.";
    } else {
        $row = $result->fetch_assoc();
        if ($password !== $row['password']) {
            $error = "Incorrect password.";
        } else {
            // Prompt for confirmation using JS
            echo "<script>
                if (confirm('Are you sure you want to delete your account?')) {
                    window.location.href = 'deletelibpfp.php?confirm=yes&user=" . urlencode($username) . "';
                } else {
                    window.location.href = 'libhome.html';
                }
            </script>";
            exit;
        }
    }
}
?>

<!-- HTML part -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Account</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function returnBack() {
            window.location.href = "libhome.html";
        }
    </script>
</head>
<body>
    <div class="main-content">
        <div class="nav">
            <img id="logo" src="nitte.png" alt="Logo">
            <p id="head">Academic Integrity Portal</p>
            <button class="logout-button" onclick="returnBack()">Return Home</button>
        </div>
        <div class="content">
            <h2>Delete Account</h2>
        </div>
        <div class="upload-card">
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form action="deletelibpfp.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br><br>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>

                <input type="submit" value="Delete Librarian" class="sb">
            </form>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 NMAM Institute Of Technology, Nitte</p>
    </footer>
</body>
</html>
