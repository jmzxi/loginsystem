<?php
// recover_reset.php
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$new_password = $_POST['new_password'] ?? '';

if (!$email || !$otp || !$new_password) {
    jsonResponse(false, "All fields are required.");
}

// Verify OTP
$stmt = $pdo->prepare("SELECT * FROM otps WHERE email = ? AND code = ? AND purpose = 'recover_password' AND expires_at > NOW() AND used = 0");
$stmt->execute([$email, $otp]);
$otpData = $stmt->fetch();

if (!$otpData) {
    jsonResponse(false, "Invalid or expired OTP.");
}

$passwordHash = password_hash($new_password, PASSWORD_DEFAULT);

$update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$update->execute([$passwordHash, $email]);

$updateOtp = $pdo->prepare("UPDATE otps SET used = 1 WHERE id = ?");
$updateOtp->execute([$otpData['id']]);

jsonResponse(true, "Password updated successfully.");
