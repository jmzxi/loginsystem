<?php
require_once 'config.php';
require_once 'helpers.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Invalid request method.");
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);  // <-- fix here

if (!$username || !$password) {
    jsonResponse(false, "Username and password required.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(false, "Invalid username or password.");
}

if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
    jsonResponse(false, "Account locked. Try again later or recover password.");
}

if (!password_verify($password, $user['password_hash'])) {
    $failed = $user['failed_login_attempts'] + 1;
    $lockedUntil = null;

    if ($failed >= 5) {
        $lockedUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $failed = 0;
    }

    $update = $pdo->prepare("UPDATE users SET failed_login_attempts = ?, last_failed_login = NOW(), locked_until = ? WHERE id = ?");
    $update->execute([$failed, $lockedUntil, $user['id']]);

    jsonResponse(false, "Invalid username or password.");
}

$update = $pdo->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?");
$update->execute([$user['id']]);

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

if ($remember) {
    $series = bin2hex(random_bytes(32));
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, series, token_hash, user_agent, ip_address, created_at, expires_at) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->execute([$user['id'], $series, $tokenHash, $userAgent, $ip, $expiresAt]);

    setcookie('remember_series', $series, time() + 60*60*24*30, '/', '', false, true);
    setcookie('remember_token', $token, time() + 60*60*24*30, '/', '', false, true);
}

// For normal form submit flow, redirect after login
header("Location: ./main-page.html");
exit;

// OR for AJAX call:
// jsonResponse(true, "Login successful.");
