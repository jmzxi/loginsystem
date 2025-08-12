<?php
// register.php
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$otp = trim($_POST['otp'] ?? '');

if (!$username || !$email || !$password || !$otp) {
    jsonResponse(false, "All fields are required.");
}

// Check username/email uniqueness
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    jsonResponse(false, "Username or email already taken.");
}

// Verify OTP
$stmt = $pdo->prepare("SELECT * FROM otps WHERE email = ? AND code = ? AND purpose = 'verify_registration' AND expires_at > NOW() AND used = 0");
$stmt->execute([$email, $otp]);
$otpData = $stmt->fetch();

if (!$otpData) {
    jsonResponse(false, "Invalid or expired OTP.");
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$insert = $pdo->prepare("INSERT INTO users (username, email, password_hash, registered_at, is_verified) VALUES (?, ?, ?, NOW(), 1)");
$insert->execute([$username, $email, $passwordHash]);

$userId = $pdo->lastInsertId();

$updateOtp = $pdo->prepare("UPDATE otps SET used = 1 WHERE id = ?");
$updateOtp->execute([$otpData['id']]);

jsonResponse(true, "Registration successful. You may now log in.");
