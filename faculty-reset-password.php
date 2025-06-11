<?php

$token = $_GET["token"];

$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "/config.php";

$sql = "SELECT * FROM faculty
        WHERE reset_token_hash = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if ($user === null) {
    die("Token has expired. Please try to reset your password again,");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("token has expired");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">

    <script>
        function returnBack() {
            window.location.href = "index.html";
        }
    </script>
</head>
<body>
    <div class="nav">
      <img id="logo" src="nitte.png" />
      <p id="head">Academic Integrity Portal</p>
      <button class="logout-button" onclick="returnBack()">Return Back</button>
    </div>
    <div class="content">
            <h2>Reset Password</h2>
        </div>
    <div class="upload-card">
    <form method="post" action="faculty-process-reset-password.php">

        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="password">New password</label>
        <input type="password" id="password" name="password">

        <label for="password_confirmation">Repeat password</label>
        <input type="password" id="password_confirmation"
               name="password_confirmation">

        <input type="submit" value="Reset" class="sb">
    </form>
    </div>
    <footer>
      <p>Copyright &copy; 2024<br> NMAM Institute Of Technology, Nitte</p>
    </footer>

</body>
</html>