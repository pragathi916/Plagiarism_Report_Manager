<?php

$token = $_POST["token"];
$token_hash = hash("sha256", $token);
$mysqli = require __DIR__ . "/config.php";

$sql = "SELECT * FROM faculty WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Invalid token or the token has already expired. Please try to reset your password again ";
    exit;
}

$password_plain = $_POST["password"];
$sql = "UPDATE faculty 
        SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL
        WHERE username = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $password_plain, $user["username"]);
$stmt->execute();

echo "<script>
    alert('Password updated successfully. You can now login.');
    window.location.href = 'faculty_login.html';
</script>";
?>