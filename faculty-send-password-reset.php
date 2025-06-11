<?php

$email = $_POST["email"];
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 10); 

$mysqli = require __DIR__ . "/config.php";

$sql = "UPDATE faculty
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

if ($mysqli->affected_rows > 0) {
    // Email exists, proceed with sending email
    $mail = require __DIR__ . "/mailer.php";
    $mail->setFrom("nmamitcollege@gmail.com", "NMAMIT Plagiarism System");
    $mail->addAddress($email);
    $mail->Subject = "Reset Your Faculty Account Password";
    $mail->isHTML(true);

    $resetLink = "http://localhost/clg/faculty-reset-password.php?token=$token";

    $mail->Body = <<<HTML
    <div style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        <h2>Password Reset Request</h2>
        <p>Dear Faculty Member,</p>
        <p>We received a request to reset the password for your account associated with this email address.</p>
        <p>Please click the button below to reset your password. This link will expire in 10 minutes.</p>
        <p style="text-align: center;">
            <a href="$resetLink" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Reset Password
            </a>
        </p>
        <p>If you did not request this password reset, you can safely ignore this email.</p>
        <br>
        <p>Regards,<br>NMAMIT Plagiarism System Team</p>
    </div>
    HTML;

    try {
        $mail->send();
        echo "<script>alert('Password reset email sent successfully. Please check your inbox.');</script>";
        echo '<meta http-equiv="refresh" content="2;url=faculty_login.html">';
    } catch (Exception $e) {
        echo "<script>alert('Failed to send email. Mailer Error: {$mail->ErrorInfo}');</script>";
    }

} else {
    // Email not found
    echo "<script>alert('No faculty account found with this email. Please check and try again.');</script>";
    echo '<meta http-equiv="refresh" content="2;url=faculty_login.html">';
}
