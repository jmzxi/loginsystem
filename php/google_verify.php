<?php
// google_verify.php
require_once 'helpers.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$id_token = $_POST['id_token'] ?? '';
if (!$id_token) {
    jsonResponse(false, "Missing Google ID token.");
}

$CLIENT_ID = GOOGLE_CLIENT_ID;

$google_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);
$response = file_get_contents($google_url);
if ($response === false) {
    jsonResponse(false, "Failed to verify token.");
}

$tokenInfo = json_decode($response, true);
if (!isset($tokenInfo['aud']) || $tokenInfo['aud'] !== $CLIENT_ID) {
    jsonResponse(false, "Token audience mismatch.");
}

$email = $tokenInfo['email'] ?? '';
$google_sub = $tokenInfo['sub'] ?? '';
$first_name = $tokenInfo['given_name'] ?? '';
$last_name = $tokenInfo['family_name'] ?? '';

if (!$email || !$google_sub) {
    jsonResponse(false, "Invalid token info.");
}

// Check if user exists by google_sub or email
$stmt = $pdo->prepare("SELECT * FROM users WHERE google_sub = ? OR email = ?");
$stmt->execute([$google_sub, $email]);
$user = $stmt->fetch();

if (!$user) {
    // Register new user with google info
    $insert = $pdo->prepare("INSERT INTO users (first_name, last_name, email, username, google_sub, registered_at, is_verified, user_type) VALUES (?, ?, ?, ?, ?, NOW(), 1, 'u')");
    $username = explode('@', $email)[0];
    $insert->execute([$first_name, $last_name, $email, $username, $google_sub]);

    $userId = $pdo->lastInsertId();
} else {
    $userId = $user['id'];
}

// Log in the user
$_SESSION['user_id'] = $userId;
$_SESSION['user_email'] = $email;

jsonResponse(true, "Google login successful.");
