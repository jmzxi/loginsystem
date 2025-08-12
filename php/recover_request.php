<?php
// recover_request.php
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$email = trim($_POST['email'] ?? '');

if (!$email) {
    jsonResponse(false, "Email is required.");
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(false, "No user found with this email.");
}

$code = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $pdo->prepare("INSERT INTO otps (user_id, email, code, purpose, expires_at, created_at) VALUES (?, ?, ?, 'recover_password', ?, NOW())");
$stmt->execute([$user['id'], $email, $code, $expiresAt]);

if (!sendOtpEmail($email, $code, 'Password Recovery OTP')) {
    jsonResponse(false, "Failed to send OTP email.");
}

jsonResponse(true, "OTP sent to your email.");
