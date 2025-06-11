<?php

$token = $_POST["token"];
$token_hash = hash("sha256", $token);
$mysqli = require __DIR__ . "/config.php";

// Find the user by reset_token_hash
$sql = "SELECT * FROM librarian WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Invalid or expired token.";
    exit;
}

// DO NOT hash the password, store as plain text (NOT RECOMMENDED)
$password_plain = $_POST["password"];

// Update the password and clear the reset token fields
$sql = "UPDATE librarian 
        SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL
        WHERE username = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $password_plain, $user["username"]);
$stmt->execute();

echo "<script>
    alert('Password updated. You can now login.');
    window.location.href = 'librarian_login.html';
</script>";
?>