<?php
// verify_otp.php
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$purpose = $_POST['purpose'] ?? '';

if (!$email || !$otp || !in_array($purpose, ['verify_registration', 'recover_password'])) {
    jsonResponse(false, "Invalid input.");
}

$stmt = $pdo->prepare("SELECT * FROM otps WHERE email = ? AND code = ? AND purpose = ? AND expires_at > NOW() AND used = 0");
$stmt->execute([$email, $otp, $purpose]);
$otpData = $stmt->fetch();

if (!$otpData) {
    jsonResponse(false, "Invalid or expired OTP.");
}

// Mark OTP as used
$updateOtp = $pdo->prepare("UPDATE otps SET used = 1 WHERE id = ?");
$updateOtp->execute([$otpData['id']]);

jsonResponse(true, "OTP verified.");
