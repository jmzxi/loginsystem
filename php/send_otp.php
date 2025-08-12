<?php
// send_otp.php
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$email = trim($_POST['email'] ?? '');
$purpose = $_POST['purpose'] ?? '';

if (!$email || !in_array($purpose, ['verify_registration', 'recover_password'])) {
    jsonResponse(false, "Invalid input.");
}

// Check if user exists for recover_password, or not exists for verify_registration
if ($purpose === 'recover_password') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        jsonResponse(false, "No account found with that email.");
    }
} else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(false, "Email already registered.");
    }
}

$code = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt = $pdo->prepare("INSERT INTO otps (email, code, purpose, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$email, $code, $purpose, $expiresAt]);

if (sendOtpEmail($email, $code, $purpose === 'recover_password' ? 'Password Recovery OTP' : 'Registration OTP')) {
    jsonResponse(true, "OTP sent to your email.");
} else {
    jsonResponse(false, "Failed to send OTP email.");
}
